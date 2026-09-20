<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBankImport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class BankImportController extends Controller
{
    public function preview(Request $request)
    {
        if (!Gate::allows('pro-user')) {
            abort(403, 'Bank Statement Import is restricted to Pro users.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $rawRows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $collection = Excel::toCollection(new Collection(), $file);
            $rawRows = $collection->first()?->toArray() ?? [];
        } else {
            $handle = fopen($file->getRealPath(), 'r');
            while (($row = fgetcsv($handle)) !== false) {
                $rawRows[] = $row;
            }
            fclose($handle);
        }

        if (empty($rawRows)) {
            return response()->json(['message' => 'The uploaded file is empty.'], 422);
        }

        // Take out header
        $rawHeaders = array_shift($rawRows);

        // Filter rows keep ones that are not empty
        $rows = array_filter($rawRows, function ($row) {
            if (!is_array($row))
                return false;
            // Check if theres atleast 1 value that isnt empty
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    return true;
                }
            }
            return false;
        });

        // Reset array values after filtering
        $rows = array_values($rows);

        // Check if file is not empty
        if (empty($rows)) {
            return response()->json(['message' => 'The uploaded file contains no data rows.'], 422);
        }

        $sampleRows = array_slice($rows, 0, 3);

        // Filter empty columns
        $headers = [];
        foreach ($rawHeaders as $index => $header) {
            $trimmedHeader = trim((string) $header);
            if ($trimmedHeader !== '') {
                $headers[$index] = $trimmedHeader;
            }
        }

        // If headers dont have valid columns
        if (empty($headers)) {
            return response()->json(['message' => 'The uploaded file has no valid column headers.'], 422);
        }

        $currencies = [];

        try {
            $response = Http::timeout(3)->get("https://api.frankfurter.dev/v1/currencies");

            if (!$response->successful()) {
                return response()->json(['message' => 'Failed to fetch currency list. Please try again.'], 500);
            }

            foreach ($response->json() as $code => $name) {
                $currencies[] = [
                    'value' => $code,
                    'label' => "{$code} - {$name}"
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Currency API error in preview: " . $e->getMessage());
            return response()->json(['message' => 'Unable to connect to currency exchange service.'], 500);
        }

        usort($currencies, function ($a, $b) {
            return strcmp($a['value'], $b['value']);
        });

        session([
            'import_headers' => $headers,
            'import_rows' => $rows,
        ]);

        return response()->json([
            'headers' => $headers,
            'sample' => $sampleRows,
            'currencies' => $currencies,
        ]);
    }
    public function store(Request $request)
    {
        if (!Gate::allows('pro-user')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'amount_col' => 'required|integer',
            'description_col' => 'required|integer',
            'currency' => 'required|string|size:3',
            'date_col' => 'nullable|integer',
        ]);

        $rows = session('import_rows');

        if (!$rows || empty($rows)) {
            return back()->withErrors(['file' => 'Import session expired. Please upload the file again.']);
        }

        // Send data for queue worker
        ProcessBankImport::dispatch(
            auth()->id(),
            $rows,
            $request->only(['amount_col', 'description_col', 'date_col']),
            $request->currency
        );

        session()->forget(['import_headers', 'import_rows']);

        return back()->with('success', 'Import is running in background! Your Transactions will be available shortly!');
    }
    public function cancel()
    {
        session()->forget(['import_headers', 'import_rows']);
        return response()->json(['message' => 'Import session cleared.']);
    }
}
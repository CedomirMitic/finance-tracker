<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBankImport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use App\Services\CurrencyService;
use Maatwebsite\Excel\Facades\Excel;

class BankImportController extends Controller
{
    public function preview(Request $request, CurrencyService $currencyService)
    {
        if (!Gate::allows('pro-user')) {
            abort(403, 'Bank Statement Import is restricted to Pro users.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $rawRows = $this->extractRowsFromFile($file);

        if (empty($rawRows)) {
            return response()->json(['message' => 'The uploaded file is empty.'], 422);
        }

        $rawHeaders = array_shift($rawRows);
        $rows = $this->filterEmptyRows($rawRows);

        if (empty($rows)) {
            return response()->json(['message' => 'The uploaded file contains no data rows.'], 422);
        }

        $headers = $this->filterHeaders($rawHeaders);

        if (empty($headers)) {
            return response()->json(['message' => 'The uploaded file has no valid column headers.'], 422);
        }

        $currencies = $currencyService->getSupportedCurrencies();
        if ($currencies instanceof \Illuminate\Http\JsonResponse) {
            return $currencies;
        }

        session([
            'import_headers' => $headers,
            'import_rows' => $rows,
        ]);

        return response()->json([
            'headers' => $headers,
            'sample' => array_slice($rows, 0, 3),
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

    private function extractRowsFromFile($file): array
    {
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, ['xlsx', 'xls'])) {
            $collection = Excel::toCollection(new Collection(), $file);
            return $collection->first()?->toArray() ?? [];
        }

        $rawRows = [];
        $handle = fopen($file->getRealPath(), 'r');
        while (($row = fgetcsv($handle)) !== false) {
            $rawRows[] = $row;
        }
        fclose($handle);

        return $rawRows;
    }

    private function filterEmptyRows(array $rawRows): array
    {
        $rows = array_filter($rawRows, function ($row) {
            if (!is_array($row))
                return false;

            foreach ($row as $cell) {
                if (trim((string) $cell) !== '')
                    return true;
            }
            return false;
        });

        return array_values($rows);
    }

    private function filterHeaders(array $rawHeaders): array
    {
        $headers = [];
        foreach ($rawHeaders as $index => $header) {
            $trimmed = trim((string) $header);
            if ($trimmed !== '') {
                $headers[$index] = $trimmed;
            }
        }
        return $headers;
    }
}
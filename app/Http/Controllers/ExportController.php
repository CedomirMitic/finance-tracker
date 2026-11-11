<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ExportController extends Controller
{
    public function index()
    {
        return Inertia::render('Export/Index');
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric|between:2000,2100',
            'format' => 'required|in:csv',
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $transactions = $request->user()->transactions()
            ->inMonth($year, $month)
            ->orderByRaw(Transaction::EFFECTIVE_DATE . ' asc')
            ->get();

        $fileName = "report_{$request->year}_{$request->month}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Category', 'Description', 'Type', 'Amount', 'Currency']);

            $safe = fn($value) => preg_match('/^[=+\-@]/', (string) $value) ? "'" . $value : $value;

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->effective_date,
                    $safe($t->category),
                    $safe($t->description),
                    $t->type,
                    $t->amount,
                    $t->currency,
                ]);
            }
            fclose($file);
        }, $fileName, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function downloadPdf(Request $request)
    {

        if (!Gate::allows('pro-user')) {
            abort(403, 'PDF Export is restricted to Pro users.');
        }
        $validated = $request->validate([
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric|min:2000|max:2100',
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $transactions = $request->user()->transactions()
            ->inMonth($year, $month)
            ->orderByRaw(Transaction::EFFECTIVE_DATE . ' asc')
            ->get();

        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $totalIncome = $transactions->where('type', 'income')->sum('amount');

        $pdf = Pdf::loadView('pdf.report', [
            'transactions' => $transactions,
            'year' => $request->year,
            'month' => $request->month,
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome
        ]);

        $fileName = "Report-{$request->year}-{$request->month}.pdf";
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($output),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
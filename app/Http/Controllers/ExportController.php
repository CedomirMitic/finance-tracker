<?php

namespace App\Http\Controllers;

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
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|digits:4',
            'format' => 'required|in:csv,pdf',
        ]);

        $transactions = $request->user()->transactions()
            ->whereYear('created_at', $request->year)
            ->whereMonth('created_at', $request->month)
            ->orderBy('created_at', 'asc')
            ->get();

        $fileName = "report_{$request->year}_{$request->month}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Category', 'Description', 'Type', 'Amount', 'Currency']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->created_at->format('Y-m-d'),
                    $t->category,
                    $t->description,
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

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|digits:4',
        ]);

        $transactions = $request->user()->transactions()
            ->whereYear('created_at', $request->year)
            ->whereMonth('created_at', $request->month)
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
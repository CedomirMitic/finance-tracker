<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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
            'month' => 'required',
            'year' => 'required',
            'format' => 'required|in:csv',
        ]);

        $userId = auth()->id();
        $transactions = Transaction::where('user_id', $userId)
            ->whereYear('created_at', $request->year)
            ->whereMonth('created_at', $request->month)
            ->orderBy('created_at', 'asc')
            ->get();

        $fileName = "report_{$request->year}_{$request->month}.csv";

        return response()->streamDownload(function() use($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Category', 'Description', 'Type', 'Amount']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->created_at->format('Y-m-d'),
                    $t->category,
                    $t->description,
                    $t->type,
                    $t->amount
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
        $userId = auth()->id();
        
        $transactions = Transaction::where('user_id', $userId)
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

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"'
        ]);
    }
}
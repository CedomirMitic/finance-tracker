<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body, table, th, td, h1, p, div { 
            font-family: 'DejaVu Sans', sans-serif; 
            color: #1e293b; 
            line-height: 1.5; 
        }
        
        body { background-color: #ffffff; margin: 0; padding: 20px; }
        
        /* Header Styling */
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Section Titles */
        .section-title { font-size: 13px; font-weight: bold; margin-bottom: 10px; color: #0f172a; border-left: 4px solid #059669; padding-left: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Summary Table */
        .summary-table { width: 55%; margin-bottom: 30px; border-collapse: collapse; }
        .summary-table th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 10px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .summary-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        
        /* Main Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 11px; }
        th { background-color: #059669; color: #ffffff; padding: 10px; text-align: left; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Amounts & Colors */
        .amount { font-weight: bold; text-align: right; white-space: nowrap; }
        .expense { color: #dc2626; }
        .income { color: #059669; }
        
        /* Footer Totals Box */
        .footer-totals { background: #f8fafc; padding: 15px 20px; border-radius: 8px; text-align: right; border: 1px solid #e2e8f0; }
        .footer-totals p { margin: 4px 0; font-size: 12px; color: #475569; font-weight: bold; }
        .grand-total { font-size: 15px; font-weight: bold; color: #0f172a; margin-top: 8px; border-top: 1px solid #cbd5e1; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report</h1>
        <p>Period: {{ $month }}/{{ $year }}</p>
    </div>

    <div class="section-title">Category Summary</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Category</th>
                <th style="text-align: right;">Total Spent</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions->where('type', 'expense')->groupBy('category') as $category => $items)
                <tr>
                    <td style="text-transform: capitalize; font-weight: bold; color: #334155;">{{ $category }}</td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">
                        {{ number_format($items->sum('amount'), 2) }} {{ $items->first()->currency }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Transaction Details</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 25%;">Category</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
                <tr>
                    <td style="color: #64748b;">{{ $t->created_at->format('d.m.Y') }}</td>
                    <td style="text-transform: capitalize; font-weight: bold; color: #475569;">{{ $t->category }}</td>
                    <td style="color: #1e293b;">{{ $t->description }}</td>
                    <td class="amount {{ $t->type === 'expense' ? 'expense' : 'income' }}">
                        {{ $t->type === 'expense' ? '-' : '+' }}{{ number_format($t->amount, 2) }} {{ $t->currency }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-totals">
        @php $currency = $transactions->first()->currency ?? 'EUR'; @endphp
        
        <p>Total Income: <span class="income">+{{ number_format($totalIncome, 2) }} {{ $currency }}</span></p>
        <p>Total Expenses: <span class="expense">-{{ number_format($totalExpenses, 2) }} {{ $currency }}</span></p>
        <div class="grand-total">
            Net Balance: 
            <span class="{{ ($totalIncome - $totalExpenses) >= 0 ? 'income' : 'expense' }}">
                {{ number_format($totalIncome - $totalExpenses, 2) }} {{ $currency }}
            </span>
        </div>
    </div>
</body>
</html>
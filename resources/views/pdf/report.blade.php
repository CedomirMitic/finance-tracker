<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #6366f1; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #4f46e5; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0 0; color: #6b7280; font-size: 14px; }
        
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 15px; color: #111827; border-left: 4px solid #6366f1; padding-left: 10px; }
        
        /* Summary Table */
        .summary-table { width: 50%; margin-bottom: 40px; margin-left: 0; }
        .summary-table th { background: #f9fafb; color: #4b5563; font-size: 12px; }
        
        /* Main Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 12px; }
        th { background-color: #4f46e5; color: white; padding: 12px 10px; text-align: left; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #fcfcfd; }
        
        .amount { font-weight: bold; text-align: right; white-space: nowrap; }
        .expense { color: #dc2626; }
        .income { color: #16a34a; }
        
        .footer-totals { background: #f3f4f6; padding: 20px; border-radius: 8px; text-align: right; }
        .footer-totals p { margin: 5px 0; font-size: 14px; }
        .grand-total { font-size: 18px; font-weight: bold; color: #111827; margin-top: 10px; border-top: 1px solid #d1d5db; padding-top: 10px; }
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
                    <td style="text-transform: capitalize;">{{ $category }}</td>
                    <td style="text-align: right; font-weight: bold;">
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
                <th style="width: 20%;">Category</th>
                <th style="width: 45%;">Description</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('d.m.Y') }}</td>
                    <td style="text-transform: capitalize; color: #6b7280;">{{ $t->category }}</td>
                    <td>{{ $t->description }}</td>
                    <td class="amount {{ $t->type === 'expense' ? 'expense' : 'income' }}">
                        {{ $t->type === 'expense' ? '-' : '+' }}{{ number_format($t->amount, 2) }} {{ $t->currency }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-totals">
        {{-- Ako imaš prosleđenu varijablu sa valutom korisnika, zameni $transactions->first()->currency sa npr. $userCurrency --}}
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
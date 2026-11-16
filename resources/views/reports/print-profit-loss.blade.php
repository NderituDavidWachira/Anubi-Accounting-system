@extends('reports.print-layout')

@section('title', 'Profit & Loss Statement')
@section('report-title', 'Profit & Loss Statement')
@section('report-period', 'Period: ' . \Carbon\Carbon::parse($fromDate)->format('d M Y') . ' to ' . \Carbon\Carbon::parse($toDate)->format('d M Y'))

@section('content')
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Operating Revenue / Income Account</th>
                <th class="text-right">Amount (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($revenues as $rev)
                <tr>
                    <td>{{ $rev['code'] }}</td>
                    <td>{{ $rev['name'] }}</td>
                    <td class="text-right">{{ number_format($rev['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="color: #64748b;">No operational revenue recorded in this period.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2">TOTAL OPERATING REVENUE</td>
                <td class="text-right">KSh {{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Operating Expense Account</th>
                <th class="text-right">Amount (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $exp)
                <tr>
                    <td>{{ $exp['code'] }}</td>
                    <td>{{ $exp['name'] }}</td>
                    <td class="text-right">{{ number_format($exp['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="color: #64748b;">No operational expenses recorded in this period.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2">TOTAL OPERATING EXPENSES</td>
                <td class="text-right">KSh {{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tbody>
            <tr class="total-row" style="font-size: 15px;">
                <td>
                    NET OPERATING RESULT
                    <span style="font-size: 11px; font-weight: normal; color: #64748b;">(Excludes Opening Capital & Balance Sheet Assets)</span>
                </td>
                <td class="text-right {{ $netProfitLoss < 0 ? 'loss' : 'profit' }}">
                    {{ $netProfitLoss < 0 ? 'NET LOSS: KSh ' . number_format(abs($netProfitLoss), 2) : 'NET PROFIT: KSh ' . number_format($netProfitLoss, 2) }}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
@extends('reports.print-layout')

@section('title', 'General Ledger Statement')
@section('report-title', $account ? 'Account Statement: ' . $account->code . ' - ' . $account->name : 'General Ledger Statement')
@section('report-period', 'Period: ' . \Carbon\Carbon::parse($fromDate)->format('d M Y') . ' to ' . \Carbon\Carbon::parse($toDate)->format('d M Y'))

@section('content')
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher #</th>
                <th>Account</th>
                <th>Narration / Memo</th>
                <th class="text-right">Debit (KSh)</th>
                <th class="text-right">Credit (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totDebit = 0; 
                $totCredit = 0; 
            @endphp
            
            @forelse($entries as $entry)
                @php 
                    $totDebit += $entry->debit; 
                    $totCredit += $entry->credit; 
                    
                    // Fallback check for voucher date column depending on your table schema
                    $voucherDate = $entry->voucher->date ?? ($entry->voucher->created_at ?? now());
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($voucherDate)->format('d/m/Y') }}</td>
                    <td>{{ $entry->voucher->voucher_number ?? '-' }}</td>
                    <td>{{ $entry->account->name ?? '-' }}</td>
                    <td>{{ $entry->voucher->narration ?? '-' }}</td>
                    <td class="text-right">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                    <td class="text-right">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">
                        No ledger transactions found for this date range.
                    </td>
                </tr>
            @endforelse

            @if($entries->count() > 0)
                <tr class="total-row">
                    <td colspan="4">TOTAL MOVEMENT</td>
                    <td class="text-right">KSh {{ number_format($totDebit, 2) }}</td>
                    <td class="text-right">KSh {{ number_format($totCredit, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection
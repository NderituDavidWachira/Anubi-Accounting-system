@extends('reports.print-layout')

@section('title', 'Trial Balance')
@section('report-title', 'Trial Balance')
@section('report-period', 'Period: ' . \Carbon\Carbon::parse($fromDate)->format('d M Y') . ' to ' . \Carbon\Carbon::parse($toDate)->format('d M Y'))

@section('content')
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th class="text-right">Debit (KSh)</th>
                <th class="text-right">Credit (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @php $sumDebit = 0; $sumCredit = 0; @endphp
            @foreach($accounts as $acc)
                @php
                    $d = $acc->ledgerEntries->sum('debit');
                    $c = $acc->ledgerEntries->sum('credit');
                    $sumDebit += $d;
                    $sumCredit += $c;
                @endphp
                @if($d > 0 || $c > 0)
                <tr>
                    <td>{{ $acc->code }}</td>
                    <td>{{ $acc->name }}</td>
                    <td>{{ ucfirst($acc->type) }}</td>
                    <td class="text-right">{{ $d > 0 ? number_format($d, 2) : '-' }}</td>
                    <td class="text-right">{{ $c > 0 ? number_format($c, 2) : '-' }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAL TRIAL BALANCE</td>
                <td class="text-right">KSh {{ number_format($sumDebit, 2) }}</td>
                <td class="text-right">KSh {{ number_format($sumCredit, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
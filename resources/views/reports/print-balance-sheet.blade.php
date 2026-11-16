@extends('reports.print-layout')

@section('title', 'Balance Sheet')
@section('report-title', 'Balance Sheet')
@section('report-period', 'As of: ' . \Carbon\Carbon::parse($toDate)->format('d M Y'))

@section('content')
    @php
        $assets = [];
        $liabilities = [];
        $equity = [];
        $totAssets = 0;
        $totLiab = 0;
        $totEq = 0;
        $rev = 0;
        $exp = 0;

        foreach ($accounts as $acc) {
            $d = $acc->ledgerEntries->sum('debit');
            $c = $acc->ledgerEntries->sum('credit');
            $t = strtolower($acc->type);

            if ($t === 'asset' && ($d - $c) != 0) {
                $bal = $d - $c;
                $assets[] = ['code' => $acc->code, 'name' => $acc->name, 'bal' => $bal];
                $totAssets += $bal;
            } elseif ($t === 'liability' && ($c - $d) != 0) {
                $bal = $c - $d;
                $liabilities[] = ['code' => $acc->code, 'name' => $acc->name, 'bal' => $bal];
                $totLiab += $bal;
            } elseif ($t === 'equity' && ($c - $d) != 0) {
                $bal = $c - $d;
                $equity[] = ['code' => $acc->code, 'name' => $acc->name, 'bal' => $bal];
                $totEq += $bal;
            } elseif (in_array($t, ['revenue', 'income'])) {
                $rev += ($c - $d);
            } elseif (in_array($t, ['expense', 'expenses'])) {
                $exp += ($d - $c);
            }
        }
        $netEarnings = $rev - $exp;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Assets</th>
                <th class="text-right">Balance (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $item)
                <tr>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-right">{{ number_format($item['bal'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL ASSETS</td>
                <td class="text-right">KSh {{ number_format($totAssets, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Liabilities &amp; Equity</th>
                <th class="text-right">Balance (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($liabilities as $item)
                <tr>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['name'] }} (Liability)</td>
                    <td class="text-right">{{ number_format($item['bal'], 2) }}</td>
                </tr>
            @endforeach
            @foreach($equity as $item)
                <tr>
                    <td>{{ $item['code'] }}</td>
                    <td>{{ $item['name'] }} (Equity)</td>
                    <td class="text-right">{{ number_format($item['bal'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>-</td>
                <td>Current Period Net Earnings / (Loss)</td>
                <td class="text-right">{{ number_format($netEarnings, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">TOTAL LIABILITIES &amp; EQUITY</td>
                <td class="text-right">KSh {{ number_format($totLiab + $totEq + $netEarnings, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
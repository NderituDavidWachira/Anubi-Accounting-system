@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-800">Financial Position & Asset Liquidity</h1>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">As of {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Click on any account row to open its full transaction register</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <form method="GET" class="flex items-center space-x-2 text-xs">
                <span class="text-slate-500 font-semibold">Date:</span>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" 
                       class="border border-slate-300 rounded px-2.5 py-1 text-slate-700 bg-white focus:outline-none">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-3 py-1 rounded transition">
                    Filter
                </button>
            </form>

            <button type="button" onclick="window.print()" 
                    class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold px-3.5 py-1.5 rounded transition shadow-sm">
                Export to PDF
            </button>
        </div>
    </div>

    <!-- Live Financial Position KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 1. Total Money & Assets Available -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Money & Assets Available</span>
            <div class="mt-2 text-2xl font-black font-mono text-slate-800">
                {{ $totalAssets < 0 ? '-' : '' }}KSh {{ number_format(abs($totalAssets), 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Bank accounts, M-Pesa, Cash and Equipment</p>
        </div>

        <!-- 2. Total Incurred Expenses -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Incurred Expenses</span>
            <div class="mt-2 text-2xl font-black font-mono text-slate-700">
                {{ $totalExpenses < 0 ? '-' : '' }}KSh {{ number_format(abs($totalExpenses), 2) }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Operational expenditures and paid obligations</p>
        </div>

        <!-- 3. Remaining Net Balance -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Remaining Net Balance</span>
            <div class="mt-2 text-2xl font-black font-mono {{ $netAvailableBalance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                {{ $netAvailableBalance < 0 ? '-' : '' }}KSh {{ number_format(abs($netAvailableBalance), 2) }}
            </div>
            <p class="text-[11px] {{ $netAvailableBalance < 0 ? 'text-red-500 font-semibold' : 'text-slate-500' }} mt-1">
                {{ $netAvailableBalance < 0 ? 'Deficit: Incurred expenses exceed available assets' : 'Surplus: Assets comfortably cover expenses' }}
            </p>
        </div>
    </div>

    <!-- Ledger Tables: Assets & Expenses Side-by-Side -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="printableArea">
        
        <!-- 1. ASSETS TABLE -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">1. Available Assets & Funds</h2>
                    <span class="text-[11px] text-sky-700 font-semibold">Click row to view ledger</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-slate-100">
                        <thead class="bg-slate-50/50 text-slate-500 font-semibold text-[11px]">
                            <tr>
                                <th class="py-2.5 px-4 text-left w-20">Code</th>
                                <th class="py-2.5 px-4 text-left">Account</th>
                                <th class="py-2.5 px-4 text-right w-36">Balance (KSh)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($assets as $acc)
                                @php
                                    $bal = $balances[$acc->id] ?? 0;
                                    $isGroup = in_array((int)$acc->id, $parentIds ?? [], true);
                                    $isSub = !is_null($acc->parent_id);
                                    $targetUrl = url('/reports/general-ledger?account_id=' . $acc->id . '&end_date=' . $asOfDate);
                                @endphp
                                @if($bal != 0 || !$isGroup)
                                    <tr onclick="window.location.href='{{ $targetUrl }}'" 
                                        style="cursor: pointer;" 
                                        class="hover:bg-sky-50 transition border-b border-slate-100 group">
                                        <td class="py-2.5 px-4 font-mono text-slate-600 group-hover:text-sky-700 font-medium">
                                            {{ $acc->code }}
                                        </td>
                                        <td class="py-2.5 px-4">
                                            <div class="flex items-center space-x-1.5 {{ $isSub ? 'pl-6' : '' }}">
                                                @if($isSub) <span class="text-slate-300 font-bold">&ndash;</span> @endif
                                                <span class="text-sky-700 font-semibold group-hover:underline">
                                                    {{ $acc->name }}
                                                </span>
                                                @if(!empty($acc->description))
                                                    <span class="ml-1 text-[9px] px-1.5 py-0.2 rounded bg-cyan-100 text-cyan-800 border border-cyan-200 font-medium">{{ $acc->description }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono {{ $bal < 0 ? 'text-red-600 font-bold' : 'text-slate-800 font-semibold' }} group-hover:text-sky-700">
                                            @if($bal != 0)
                                                {{ $bal < 0 ? '-' : '' }}{{ number_format(abs($bal), 2) }}
                                            @else
                                                <span class="text-slate-300 font-normal">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">No active assets registered.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs font-bold text-slate-900">
                <span>Total Company Assets</span>
                <span class="font-mono text-sm {{ $totalAssets < 0 ? 'text-red-600' : 'text-slate-900' }}">
                    {{ $totalAssets < 0 ? '-' : '' }}KSh {{ number_format(abs($totalAssets), 2) }}
                </span>
            </div>
        </div>

        <!-- 2. EXPENSES TABLE -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">2. Operational Expenses Incurred</h2>
                    <span class="text-[11px] text-sky-700 font-semibold">Click row to view ledger</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-slate-100">
                        <thead class="bg-slate-50/50 text-slate-500 font-semibold text-[11px]">
                            <tr>
                                <th class="py-2.5 px-4 text-left w-20">Code</th>
                                <th class="py-2.5 px-4 text-left">Expenditure Head</th>
                                <th class="py-2.5 px-4 text-right w-36">Amount (KSh)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($expenses as $acc)
                                @php
                                    $bal = $balances[$acc->id] ?? 0;
                                    $isGroup = in_array((int)$acc->id, $parentIds ?? [], true);
                                    $isSub = !is_null($acc->parent_id);
                                    $targetUrl = url('/reports/general-ledger?account_id=' . $acc->id . '&end_date=' . $asOfDate);
                                @endphp
                                @if($bal != 0 || !$isGroup)
                                    <tr onclick="window.location.href='{{ $targetUrl }}'" 
                                        style="cursor: pointer;" 
                                        class="hover:bg-sky-50 transition border-b border-slate-100 group">
                                        <td class="py-2.5 px-4 font-mono text-slate-600 group-hover:text-sky-700 font-medium">
                                            {{ $acc->code }}
                                        </td>
                                        <td class="py-2.5 px-4">
                                            <div class="flex items-center space-x-1.5 {{ $isSub ? 'pl-6' : '' }}">
                                                @if($isSub) <span class="text-slate-300 font-bold">&ndash;</span> @endif
                                                <span class="text-sky-700 font-semibold group-hover:underline">
                                                    {{ $acc->name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono {{ $bal < 0 ? 'text-red-600 font-bold' : 'text-slate-800 font-semibold' }} group-hover:text-sky-700">
                                            @if($bal != 0)
                                                {{ $bal < 0 ? '-' : '' }}{{ number_format(abs($bal), 2) }}
                                            @else
                                                <span class="text-slate-300 font-normal">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">No expenses recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs font-bold text-slate-900">
                <span>Total Operational Spending</span>
                <span class="font-mono text-sm {{ $totalExpenses < 0 ? 'text-red-600' : 'text-slate-900' }}">
                    {{ $totalExpenses < 0 ? '-' : '' }}KSh {{ number_format(abs($totalExpenses), 2) }}
                </span>
            </div>
        </div>

    </div>

    <!-- Net Position Summary Formula -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row justify-between items-center text-xs">
        <div class="space-y-0.5 mb-3 sm:mb-0">
            <span class="font-bold text-slate-800 text-sm">Net Available Position Formula</span>
            <p class="text-slate-500">Net Balance = Total Holdings ({{ $totalAssets < 0 ? '-' : '' }}KSh {{ number_format(abs($totalAssets), 2) }}) &minus; Total Incurred Expenses ({{ $totalExpenses < 0 ? '-' : '' }}KSh {{ number_format(abs($totalExpenses), 2) }})</p>
        </div>
        <div class="text-right">
            <span class="block text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Remaining Funds</span>
            <span class="text-lg font-black font-mono {{ $netAvailableBalance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                {{ $netAvailableBalance < 0 ? '-' : '' }}KSh {{ number_format(abs($netAvailableBalance), 2) }}
            </span>
        </div>
    </div>
</div>

<style>
@media print {
    aside, button, form, nav {
        display: none !important;
    }
    body {
        background-color: white !important;
        color: black !important;
    }
    .ml-60 {
        margin-left: 0 !important;
    }
    #printableArea {
        display: block !important;
    }
}
</style>
@endsection
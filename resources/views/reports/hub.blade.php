@extends('layouts.app')

@section('title', 'Reports Center - Anubi Accounting System')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 pb-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Financial Reports Center</h1>
            <p class="text-xs text-slate-500 mt-0.5">Select a reporting period to generate print-ready or PDF financial statements.</p>
        </div>
        <span class="text-[11px] font-medium text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-md self-start sm:self-auto">
            Currency: KSh (KES)
        </span>
    </div>

    <!-- 2x2 Equal-Height Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card 1: Profit & Loss Statement -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h2 class="text-base font-bold text-slate-800">Profit &amp; Loss Statement (P&amp;L)</h2>
                    <span class="shrink-0 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Operating Only
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-5">
                    Calculates net profit or loss from earned income minus expenses, excluding opening capital and balance sheet assets.
                </p>
            </div>
            
            <form action="{{ route('reports.profit-loss.print') }}" method="GET" target="_blank" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                        <input type="date" name="from_date" value="{{ $defaultFrom }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                        <input type="date" name="to_date" value="{{ $defaultTo }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100">
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold py-2.5 px-4 rounded-lg transition">
                        Generate Print / PDF
                    </button>
                    <a href="{{ route('reports.profit-loss') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold py-2.5 px-4 rounded-lg transition">
                        View On Screen
                    </a>
                </div>
            </form>
        </div>

        <!-- Card 2: General Ledger / Account Statement -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h2 class="text-base font-bold text-slate-800">General Ledger / Account Statement</h2>
                    <span class="shrink-0 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200">
                        Audit Trail
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Detailed transaction history showing money in, money out, and running balances across individual or all accounts.
                </p>
            </div>
            
            <form action="{{ route('reports.general-ledger.print') }}" method="GET" target="_blank" class="space-y-3.5">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Account Filter</label>
                    <select name="account_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white focus:border-slate-800 focus:outline-none">
                        <option value="">All Accounts (Full Ledger)</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                        <input type="date" name="from_date" value="{{ $defaultFrom }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                        <input type="date" name="to_date" value="{{ $defaultTo }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold py-2.5 px-4 rounded-lg transition">
                        Generate Print / PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 3: Trial Balance -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h2 class="text-base font-bold text-slate-800">Trial Balance</h2>
                    <span class="shrink-0 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Double-Entry Check
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-5">
                    Summary of all debit and credit account balances across your chart of accounts for the selected period.
                </p>
            </div>
            
            <form action="{{ route('reports.trial-balance.print') }}" method="GET" target="_blank" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                        <input type="date" name="from_date" value="{{ $defaultFrom }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                        <input type="date" name="to_date" value="{{ $defaultTo }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold py-2.5 px-4 rounded-lg transition">
                        Generate Print / PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 4: Balance Sheet -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h2 class="text-base font-bold text-slate-800">Balance Sheet</h2>
                    <span class="shrink-0 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                        Financial Position
                    </span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed mb-5">
                    Snapshot of total assets (cash, bank, M-Pesa), liabilities, and opening or retained equity as of a specific date.
                </p>
            </div>
            
            <form action="{{ route('reports.balance-sheet.print') }}" method="GET" target="_blank" class="space-y-4">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">As Of Date</label>
                    <input type="date" name="to_date" value="{{ $defaultTo }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 focus:border-slate-800 focus:outline-none">
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold py-2.5 px-4 rounded-lg transition">
                        Generate Print / PDF
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
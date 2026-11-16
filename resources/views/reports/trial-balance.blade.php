@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Filter Bar -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-800">Trial Balance</h1>
                <span class="text-xs px-2.5 py-0.5 rounded-full {{ !empty($trialBalance['is_balanced']) ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }} font-semibold">
                    {{ !empty($trialBalance['is_balanced']) ? 'Balanced' : 'Imbalance Detected' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Summary of all debit and credit account balances verifying double-entry equality</p>
        </div>

        <div class="flex items-center space-x-3">
            <form method="GET" class="flex items-center space-x-2 text-xs">
                <span class="text-slate-500 font-semibold">As of:</span>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" 
                       class="border border-slate-300 rounded px-2.5 py-1 text-slate-700 bg-white focus:outline-none">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-3 py-1 rounded transition">
                    Run Report
                </button>
            </form>

            <button type="button" onclick="window.print()" 
                    class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-semibold px-3 py-1.5 rounded transition">
                Print / Export
            </button>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left divide-y divide-slate-200">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-5 w-28">Account Code</th>
                        <th class="py-3 px-5">Account Name</th>
                        <th class="py-3 px-5 w-32">Type</th>
                        <th class="py-3 px-5 text-right w-44">Debit (KSh)</th>
                        <th class="py-3 px-5 text-right w-44">Credit (KSh)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($trialBalance['entries'] as $entry)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2.5 px-5 font-mono text-slate-600">{{ $entry['code'] }}</td>
                            <td class="py-2.5 px-5 font-medium text-slate-800">{{ $entry['name'] }}</td>
                            <td class="py-2.5 px-5 capitalize text-slate-500">
                                {{ $entry['type'] === 'revenue' ? 'Income' : ucfirst($entry['type']) }}
                            </td>
                            <td class="py-2.5 px-5 text-right font-mono">
                                {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                            </td>
                            <td class="py-2.5 px-5 text-right font-mono">
                                {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No transactions posted to the ledger yet.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="3" class="py-3 px-5 uppercase text-right tracking-wider">Total Ledger Activity:</td>
                        <td class="py-3 px-5 text-right font-mono border-b-4 border-double border-slate-900">
                            KSh {{ number_format($trialBalance['total_debit'] ?? 0, 2) }}
                        </td>
                        <td class="py-3 px-5 text-right font-mono border-b-4 border-double border-slate-900">
                            KSh {{ number_format($trialBalance['total_credit'] ?? 0, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-500 flex justify-between items-center">
            <span>Difference: KSh {{ number_format(abs(($trialBalance['total_debit'] ?? 0) - ($trialBalance['total_credit'] ?? 0)), 2) }}</span>
            <span class="{{ !empty($trialBalance['is_balanced']) ? 'text-emerald-700 font-bold' : 'text-red-600 font-bold' }}">
                {{ !empty($trialBalance['is_balanced']) ? '✓ Ledgers Balance Exactly' : '⚠ Warning: Out of Balance' }}
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
}
</style>
@endsection
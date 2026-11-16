@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Selection Bar -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('reports.balance-sheet') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium flex items-center space-x-1">
                    <span>&larr; Back to Balance Sheet</span>
                </a>
            </div>
            <h1 class="text-xl font-bold text-slate-800 mt-1">
                Account QuickReport: 
                <span class="text-sky-700">
                    {{ $accounts->firstWhere('id', $selectedAccountId)?->code }} - {{ $accounts->firstWhere('id', $selectedAccountId)?->name ?? 'Select Account' }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Full audit trail of how money entered or was spent from this account</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <form method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                <select name="account_id" onchange="this.form.submit()" class="border border-slate-300 rounded px-2.5 py-1 text-slate-700 bg-white focus:outline-none">
                    <option value="">-- Choose Account --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                            {{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})
                        </option>
                    @endforeach
                </select>

                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-slate-300 rounded px-2 py-1 text-slate-700 bg-white focus:outline-none">
                <span class="text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-slate-300 rounded px-2 py-1 text-slate-700 bg-white focus:outline-none">
                
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-3 py-1 rounded transition">
                    Filter
                </button>
            </form>

            <button type="button" onclick="window.print()" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-semibold px-3 py-1 rounded transition">
                Print
            </button>
        </div>
    </div>

    <!-- QuickReport Transaction Register Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left divide-y divide-slate-200">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-5 w-24">Date</th>
                        <th class="py-3 px-5 w-28">Voucher #</th>
                        <th class="py-3 px-5 w-28">Type</th>
                        <th class="py-3 px-5">Narration / Memo</th>
                        <th class="py-3 px-5 text-right w-32">Money In (Debit)</th>
                        <th class="py-3 px-5 text-right w-32">Money Out (Credit)</th>
                        <th class="py-3 px-5 text-right w-36">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php $running = 0; @endphp
                    @forelse($entries as $entry)
                        @php
                            $debit = (float)$entry->debit;
                            $credit = (float)$entry->credit;
                            $account = $accounts->firstWhere('id', $selectedAccountId);
                            if ($account && in_array($account->type, ['asset', 'expense'])) {
                                $running += ($debit - $credit);
                            } else {
                                $running += ($credit - $debit);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2.5 px-5 font-mono text-slate-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($entry->voucher->transaction_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-2.5 px-5 font-mono text-sky-800 font-medium">
                                <a href="{{ route('vouchers.show', $entry->voucher_id) }}" class="hover:underline">
                                    {{ $entry->voucher->voucher_number }}
                                </a>
                            </td>
                            <td class="py-2.5 px-5 uppercase text-[10px] font-semibold text-slate-500">
                                {{ $entry->voucher->type }}
                            </td>
                            <td class="py-2.5 px-5">
                                <span class="font-medium text-slate-800">{{ $entry->voucher->narration }}</span>
                                @if(!empty($entry->line_memo))
                                    <span class="block text-[11px] text-slate-400 italic">{{ $entry->line_memo }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-5 text-right font-mono text-slate-800">
                                {{ $debit > 0 ? number_format($debit, 2) : '-' }}
                            </td>
                            <td class="py-2.5 px-5 text-right font-mono text-slate-800">
                                {{ $credit > 0 ? number_format($credit, 2) : '-' }}
                            </td>
                            <td class="py-2.5 px-5 text-right font-mono font-bold {{ $running < 0 ? 'text-red-600' : 'text-slate-900' }}">
                                {{ $running < 0 ? '-' : '' }}KSh {{ number_format(abs($running), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                @if($selectedAccountId)
                                    No transactions recorded for this account.
                                @else
                                    Please select an account above to view its transaction history.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
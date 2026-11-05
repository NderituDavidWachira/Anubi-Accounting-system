@extends('layouts.app')

@section('content')
<div class="bg-white rounded border border-gray-300 p-6 shadow-sm">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 mb-6 border-b border-gray-200">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">General Ledger</h1>
            <p class="text-xs text-gray-500 mt-0.5">Account statement ledger and running balances</p>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-2 mt-4 md:mt-0 text-xs">
            <select name="account_id" class="border border-gray-300 rounded px-2.5 py-1.5 bg-white text-gray-700">
                <option value="">-- Select Account --</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} - {{ $acc->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded px-2 py-1 bg-white text-gray-700">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded px-2 py-1 bg-white text-gray-700">
            <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white font-medium px-3 py-1.5 rounded transition">Filter</button>
        </form>
    </div>

    @if($selectedAccountId && $entries->count() > 0)
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-gray-100 text-gray-600 font-semibold uppercase">
                <tr>
                    <th class="p-2.5 text-left">Date</th>
                    <th class="p-2.5 text-left">Voucher #</th>
                    <th class="p-2.5 text-left">Narration / Memo</th>
                    <th class="p-2.5 text-right w-28">Debit</th>
                    <th class="p-2.5 text-right w-28">Credit</th>
                    <th class="p-2.5 text-right w-32">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($entries as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2.5 text-gray-600 font-mono">{{ $entry->voucher->transaction_date->format('Y-m-d') }}</td>
                        <td class="p-2.5 font-medium text-slate-700 font-mono">{{ $entry->voucher->voucher_number }}</td>
                        <td class="p-2.5 text-gray-600">{{ $entry->line_memo ?? $entry->voucher->narration }}</td>
                        <td class="p-2.5 text-right font-mono">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                        <td class="p-2.5 text-right font-mono">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                        <td class="p-2.5 text-right font-mono font-semibold text-gray-800">{{ number_format($entry->running_balance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="py-12 text-center text-xs text-gray-400">
            {{ $selectedAccountId ? 'No approved transactions found for this period.' : 'Select an account from the dropdown above to load its statement.' }}
        </div>
    @endif
</div>
@endsection
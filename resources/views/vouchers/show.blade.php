@extends('layouts.app')

@section('content')
<div class="bg-white rounded border border-slate-200 shadow-sm p-6 max-w-4xl mx-auto text-slate-800">
    <div class="flex justify-between items-start border-b border-slate-200 pb-4 mb-6">
        <div>
            <span class="text-xs font-mono uppercase text-slate-500 font-semibold">{{ strtoupper($voucher->type) }} VOUCHER</span>
            <h1 class="text-2xl font-bold font-mono text-slate-800">{{ $voucher->voucher_number }}</h1>
            <p class="text-xs text-slate-500 mt-1">Date: {{ $voucher->transaction_date->format('Y-m-d') }}</p>
        </div>
       <div></div>
        <div class="text-right">
            <span class="px-3 py-1 rounded text-xs font-semibold {{ $voucher->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                {{ ucfirst($voucher->status) }}
            </span>
            @if($voucher->status === 'pending_approval' && auth()->user()->isAdmin())
                <form action="{{ route('vouchers.approve', $voucher) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs px-3 py-1.5 rounded font-semibold shadow-sm">
                        Approve & Post to Ledger
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 text-xs mb-6 bg-slate-50 p-4 rounded border border-slate-100">
        <div>
            <span class="text-slate-500 block uppercase tracking-wider text-[10px]">Payee / Source:</span>
            <span class="font-semibold text-slate-800">{{ $voucher->payee_or_source }}</span>
        </div>
        <div>
            <span class="text-slate-500 block uppercase tracking-wider text-[10px]">Program / Cost Center:</span>
            <span class="font-semibold text-slate-800">{{ $voucher->program->name ?? 'General Administrative' }}</span>
        </div>
        <div class="col-span-2">
            <span class="text-slate-500 block uppercase tracking-wider text-[10px]">Narration:</span>
            <span>{{ $voucher->narration }}</span>
        </div>
    </div>

    <table class="min-w-full divide-y divide-slate-200 text-xs border border-slate-200 mb-6">
        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase">
            <tr>
                <th class="p-2.5 text-left">Account Code</th>
                <th class="p-2.5 text-left">Account Name</th>
                <th class="p-2.5 text-left">Memo</th>
                <th class="p-2.5 text-right w-28">Debit</th>
                <th class="p-2.5 text-right w-28">Credit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($voucher->ledgerEntries as $entry)
                <tr>
                    <td class="p-2.5 font-mono text-slate-600">{{ $entry->account->code }}</td>
                    <td class="p-2.5 font-medium">{{ $entry->account->name }}</td>
                    <td class="p-2.5 text-slate-500">{{ $entry->line_memo ?? '-' }}</td>
                    <td class="p-2.5 text-right font-mono">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                    <td class="p-2.5 text-right font-mono">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200">
            <tr>
                <td colspan="3" class="p-2.5 text-right">Totals:</td>
                <td class="p-2.5 text-right font-mono">{{ number_format($voucher->ledgerEntries->sum('debit'), 2) }}</td>
                <td class="p-2.5 text-right font-mono">{{ number_format($voucher->ledgerEntries->sum('credit'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <div class="flex justify-between items-center text-xs text-slate-500 pt-2 border-t border-slate-100">
        <a href="{{ route('vouchers.index') }}" class="text-slate-700 hover:underline font-semibold">&larr; Back to Vouchers</a>
        <span>Created by: {{ $voucher->creator->name ?? 'System' }}</span>
    </div>
</div>
@endsection
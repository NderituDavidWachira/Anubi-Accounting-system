@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Voucher Approvals Desk</h1>
        <p class="text-sm text-slate-500">Authorize or audit transactions submitted by program coordinators</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="p-3 text-left">Voucher #</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Payee / Source</th>
                    <th class="p-3 text-left">Program</th>
                    <th class="p-3 text-left">Submitted By</th>
                    <th class="p-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($vouchers as $voucher)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-mono font-bold text-indigo-600">{{ $voucher->voucher_number }}</td>
                        <td class="p-3">{{ $voucher->transaction_date->format('Y-m-d') }}</td>
                        <td class="p-3 font-medium">{{ $voucher->payee_or_source }}</td>
                        <td class="p-3 text-slate-600">{{ $voucher->program->name ?? 'General' }}</td>
                        <td class="p-3 text-slate-600">{{ $voucher->creator->name ?? 'Staff' }}</td>
                        <td class="p-3 text-right space-x-2">
                            <form action="{{ route('vouchers.approve', $voucher) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-3 py-1.5 rounded font-bold">
                                    Approve & Post
                                </button>
                            </form>
                            <a href="{{ route('vouchers.show', $voucher) }}" class="text-slate-600 hover:underline text-xs">Inspect</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">All vouchers are authorized and posted.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $vouchers->links() }}</div>
</div>
@endsection
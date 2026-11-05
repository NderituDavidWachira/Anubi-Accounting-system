@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm border p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Financial Vouchers</h1>
            <p class="text-sm text-gray-500">Record payments, receipts, and internal transfers</p>
        </div>
        <a href="{{ route('vouchers.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow">
            + New Voucher
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Voucher #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payee / Source</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Program</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                @forelse($vouchers as $voucher)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-indigo-600">{{ $voucher->voucher_number }}</td>
                        <td class="px-4 py-3">{{ $voucher->transaction_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-bold text-gray-600">{{ $voucher->type }}</td>
                        <td class="px-4 py-3">{{ $voucher->payee_or_source }}</td>
                        <td class="px-4 py-3">{{ $voucher->program->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            @if($voucher->status === 'approved')
                                <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-800 font-medium">Approved</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-amber-100 text-amber-800 font-medium">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('vouchers.show', $voucher) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">No vouchers recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vouchers->links() }}
    </div>
</div>
@endsection
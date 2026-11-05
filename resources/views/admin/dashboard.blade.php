@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="flex justify-between items-center bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-800">System Executive Dashboard</h1>
            <p class="text-sm text-slate-500">Real-time ledger audit, approvals, and cost center supervision</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.approvals') }}" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm flex items-center">
                Pending Approvals ({{ $pendingApprovalsCount }})
            </a>
            <a href="{{ route('vouchers.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm">
                + New Voucher
            </a>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400">Ledger Status</span>
            <div class="mt-2 text-2xl font-black {{ bccomp($totalDebits, $totalCredits, 2) === 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ bccomp($totalDebits, $totalCredits, 2) === 0 ? 'Balanced' : 'Imbalance Detected' }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Total posted: ${{ number_format($totalDebits, 2) }}</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400">Needs Review</span>
            <div class="mt-2 text-2xl font-black text-amber-500">{{ $pendingApprovalsCount }}</div>
            <p class="text-xs text-slate-500 mt-1">Vouchers awaiting posting</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400">Posted Vouchers</span>
            <div class="mt-2 text-2xl font-black text-slate-800">{{ $totalPostedVouchers }}</div>
            <p class="text-xs text-slate-500 mt-1">Committed to ledger</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs uppercase font-bold text-slate-400">Cost Centers</span>
            <div class="mt-2 text-2xl font-black text-slate-800">{{ $totalPrograms }} Active</div>
            <p class="text-xs text-slate-500 mt-1">{{ $totalStaff }} Staff Operators</p>
        </div>
    </div>

    <!-- Approvals & Activity Feed Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Urgent Pending Approvals Queue -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800">Pending Actions</h2>
                <a href="{{ route('admin.approvals') }}" class="text-xs font-semibold text-emerald-600 hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($pendingVouchers as $voucher)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ $voucher->voucher_number }}</div>
                            <div class="text-xs text-slate-500">{{ $voucher->payee_or_source }} &bull; {{ $voucher->creator->name ?? 'Staff' }}</div>
                        </div>
                        <a href="{{ route('vouchers.show', $voucher) }}" class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-1 rounded hover:bg-amber-200">
                            Review
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">No vouchers pending approval.</div>
                @endforelse
            </div>
        </div>

        <!-- Master Activity Log -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800">Company Ledger Feed (All Users)</h2>
                <a href="{{ route('vouchers.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Full Register</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase">
                        <tr>
                            <th class="p-2">Number</th>
                            <th class="p-2">Date</th>
                            <th class="p-2">Creator</th>
                            <th class="p-2">Program</th>
                            <th class="p-2">Status</th>
                            <th class="p-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentVouchers as $voucher)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2 font-mono font-bold text-slate-800">{{ $voucher->voucher_number }}</td>
                                <td class="p-2">{{ $voucher->transaction_date->format('M d, Y') }}</td>
                                <td class="p-2 text-slate-600">{{ $voucher->creator->name ?? 'N/A' }}</td>
                                <td class="p-2 text-slate-600">{{ $voucher->program->code ?? 'Admin/General' }}</td>
                                <td class="p-2">
                                    @if($voucher->status === 'approved')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @endif
                                </td>
                                <td class="p-2 text-right">
                                    <a href="{{ route('vouchers.show', $voucher) }}" class="text-indigo-600 hover:underline font-semibold">Inspect</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-slate-400">No activity logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
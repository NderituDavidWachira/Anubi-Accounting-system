@extends('layouts.app')

@section('content')
<!-- Chart.js CDN for Visual Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">
    <!-- Top Bar: Clean Greeting & Quick Actions -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Financial Overview &amp; Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time ledger balances, liquid funds, and cost center activity</p>
        </div>
        <div class="flex items-center space-x-2.5">
            @if($pendingApprovalsCount > 0)
                <a href="{{ route('admin.approvals') }}" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3.5 py-2 rounded shadow-sm transition flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <span>Approvals ({{ $pendingApprovalsCount }})</span>
                </a>
            @endif
            <a href="{{ route('vouchers.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold px-3.5 py-2 rounded shadow-sm transition">
                + New Voucher
            </a>
            <a href="{{ route('reports.balance-sheet') }}" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-3.5 py-2 rounded shadow-sm transition">
                Balance Sheet
            </a>
        </div>
    </div>

    <!-- 1. Executive Position KPI Cards (Matches Balance Sheet 1:1) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Total Available Assets -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Money &amp; Assets Available</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                </div>
                <div class="mt-2 text-2xl font-black font-mono text-slate-800">
                    KSh {{ number_format($totalAssets, 2) }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                <span class="text-slate-500">Total active accounts</span>
                <span class="font-bold text-slate-700">{{ $liquidAccounts->count() }} Liquid Heads</span>
            </div>
        </div>

        <!-- Total Operational Spending -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Incurred Expenses</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                </div>
                <div class="mt-2 text-2xl font-black font-mono text-slate-800">
                    KSh {{ number_format($totalExpenses, 2) }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                <span class="text-slate-500">Committed vouchers</span>
                <span class="font-bold text-slate-700">{{ $totalPostedVouchers }} Posted</span>
            </div>
        </div>

        <!-- Remaining Net Position -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Net Operating Position</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $netPosition < 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $netPosition < 0 ? 'Deficit' : 'Surplus' }}
                    </span>
                </div>
                <div class="mt-2 text-2xl font-black font-mono {{ $netPosition < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                    {{ $netPosition < 0 ? '-' : '' }}KSh {{ number_format(abs($netPosition), 2) }}
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Formula: Total Holdings &minus; Liabilities (Net Cash &amp; Assets)
            </div>
        </div>
    </div>

    <!-- 2. Mid Section: Cash Accounts List & Donut Distribution Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Individual Bank & M-Pesa Accounts List (2 Columns wide) -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Cash, Bank &amp; Mobile Wallets</h2>
                    <span class="text-[11px] text-sky-700 font-semibold">Click any account to view transactions</span>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($liquidAccounts as $acc)
                        <div onclick="window.location.href='{{ route('reports.general-ledger', ['account_id' => $acc->id]) }}'"
                             class="p-4 flex items-center justify-between hover:bg-sky-50/70 transition cursor-pointer group">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs group-hover:bg-sky-100 group-hover:text-sky-800">
                                    {{ substr($acc->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 group-hover:text-sky-700 group-hover:underline flex items-center space-x-1.5">
                                        <span>{{ $acc->name }}</span>
                                        @if(!empty($acc->description))
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-cyan-100 text-cyan-800 font-normal no-underline">{{ $acc->description }}</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">Code: {{ $acc->code }}</div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-mono font-bold text-sm {{ $acc->current_balance < 0 ? 'text-red-600' : 'text-slate-800' }}">
                                    {{ $acc->current_balance < 0 ? '-' : '' }}KSh {{ number_format(abs($acc->current_balance), 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400">Available balance</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400">No active bank or liquid cash accounts found.</div>
                    @endforelse
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-between items-center text-xs">
                <span class="font-bold text-slate-700">Total Liquid Funds</span>
                <span class="font-mono font-bold text-slate-900 text-sm">KSh {{ number_format($totalAssets, 2) }}</span>
            </div>
        </div>

        <!-- Cash Allocation Visual Donut Chart (1 Column wide) -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-3">Funds Distribution</h2>
                <div class="relative h-48 flex items-center justify-center">
                    <canvas id="fundsChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 text-center">
                Visual breakdown of cash held across banks and M-Pesa
            </div>
        </div>
    </div>

    <!-- 3. Bottom Section: Monthly Trend Chart & Recent Vouchers Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Cash Flow Comparison (Bar Chart) -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">6-Month Inflows vs Outflows</h2>
                    <span class="text-[11px] text-slate-400">Chart.js Live Data</span>
                </div>
                <div class="h-56">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between items-center text-[11px] text-slate-500">
                <span class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-600 inline-block"></span><span>Revenues Received</span></span>
                <span class="flex items-center space-x-1.5"><span class="w-2.5 h-2.5 rounded bg-rose-500 inline-block"></span><span>Expenses Incurred</span></span>
            </div>
        </div>

        <!-- Recent Ledger Vouchers Feed -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Recent Vouchers Committed</h2>
                    <a href="{{ route('vouchers.index') }}" class="text-[11px] text-sky-700 hover:underline">View all vouchers</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($recentVouchers as $rv)
                        @php
                            $voucherDate = $rv->transaction_date ?? ($rv->date ?? $rv->created_at);
                        @endphp
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-slate-800">{{ $rv->voucher_number }}</span>
                                    <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-semibold {{ $rv->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $rv->status }}
                                    </span>
                                </div>
                                <div class="text-slate-500 text-[11px] mt-0.5 truncate max-w-xs">{{ $rv->narration }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono text-slate-600 text-[11px]">
                                    {{ \Carbon\Carbon::parse($voucherDate)->format('d M Y') }}
                                </div>
                                <a href="{{ route('vouchers.show', $rv->id) }}" class="text-[11px] text-sky-700 hover:underline font-medium">Inspect</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400">No recent vouchers posted.</div>
                    @endforelse
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-500 flex justify-between items-center">
                <span>Audit trail logged to Double-Entry Ledger</span>
                <span>Anubi Accounting System</span>
            </div>
        </div>

    </div>
</div>

<!-- ================= CHART.JS INITIALIZATION ================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Donut Chart: Funds Distribution across Liquid Accounts
    const accountNames = @json($liquidAccounts->pluck('name')->values());
    const accountBalances = @json($liquidAccounts->pluck('current_balance')->values());

    const fundsCtx = document.getElementById('fundsChart').getContext('2d');
    new Chart(fundsCtx, {
        type: 'doughnut',
        data: {
            labels: accountNames,
            datasets: [{
                data: accountBalances,
                backgroundColor: [
                    '#0284c7', // Sky Blue (Bank)
                    '#10b981', // Emerald (M-Pesa)
                    '#f59e0b', // Amber (Cash in Hand)
                    '#6366f1', // Indigo
                    '#8b5cf6'  // Purple
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        font: { size: 10 }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // 2. Bar Chart: 6-Month Inflow vs Outflow Trends
    const trendMonths = @json($months);
    const trendIncome = @json($incomeData);
    const trendExpense = @json($expenseData);

    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'bar',
        data: {
            labels: trendMonths,
            datasets: [
                {
                    label: 'Income / Inflows',
                    data: trendIncome,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                },
                {
                    label: 'Expenses / Outflows',
                    data: trendExpense,
                    backgroundColor: '#f43f5e',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10 },
                        callback: function(value) { return 'KSh ' + value.toLocaleString(); }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection
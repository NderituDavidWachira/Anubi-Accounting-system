@extends('layouts.app')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Profit & Loss Statement</h1>
            <p class="text-xs text-slate-500">Pure operational earnings and expenses (excluding opening capital and assets).</p>
        </div>
        
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="flex items-center gap-2">
            <div>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs">
            </div>
            <div>
                <input type="date" name="to_date" value="{{ $toDate }}" class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs">
            </div>
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-1.5 rounded-lg">Filter</button>
            <a href="{{ route('reports.profit-loss.print', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" target="_blank" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold px-4 py-1.5 rounded-lg">
                Print / PDF
            </a>
        </form>
    </div>

    <!-- Summary Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Revenue</span>
                <span class="text-xl font-bold text-emerald-600">KSh {{ number_format($totalRevenue, 2) }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Expenses</span>
                <span class="text-xl font-bold text-rose-600">KSh {{ number_format($totalExpenses, 2) }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Net Operating Result</span>
                <span class="text-xl font-bold {{ $netProfitLoss < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $netProfitLoss < 0 ? 'Loss: KSh ' . number_format(abs($netProfitLoss), 2) : 'Profit: KSh ' . number_format($netProfitLoss, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Detailed Breakdowns -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Revenues Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 font-bold text-xs uppercase text-slate-600">Operating Revenues</div>
            <table class="w-full text-left border-collapse">
                <tbody>
                    @forelse($revenues as $rev)
                        <tr class="border-b border-slate-100 text-xs">
                            <td class="px-6 py-3 font-mono text-slate-500">{{ $rev['code'] }}</td>
                            <td class="px-6 py-3 text-slate-800">{{ $rev['name'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-emerald-600">KSh {{ number_format($rev['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-6 text-center text-xs text-slate-400">No operational revenue recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Expenses Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 font-bold text-xs uppercase text-slate-600">Operating Expenses</div>
            <table class="w-full text-left border-collapse">
                <tbody>
                    @forelse($expenses as $exp)
                        <tr class="border-b border-slate-100 text-xs">
                            <td class="px-6 py-3 font-mono text-slate-500">{{ $exp['code'] }}</td>
                            <td class="px-6 py-3 text-slate-800">{{ $exp['name'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-rose-600">KSh {{ number_format($exp['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-6 text-center text-xs text-slate-400">No operational expenses recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Program;
use App\Models\User;
use App\Models\Voucher;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $asOfDate = now()->format('Y-m-d');

        // 1. Core KPIs (Accurate net financial position)
        $totalAssets = (float) LedgerEntry::whereHas('account', fn($q) => $q->where('type', 'asset')->where('is_active', true))
            ->whereHas('voucher', fn($q) => $q->where('status', 'approved'))
            ->selectRaw('COALESCE(SUM(debit - credit), 0) as net')
            ->value('net');

        $totalExpenses = (float) LedgerEntry::whereHas('account', fn($q) => $q->where('type', 'expense')->where('is_active', true))
            ->whereHas('voucher', fn($q) => $q->where('status', 'approved'))
            ->selectRaw('COALESCE(SUM(debit - credit), 0) as net')
            ->value('net');

        $totalIncome = (float) LedgerEntry::whereHas('account', fn($q) => $q->where('type', 'revenue')->where('is_active', true))
            ->whereHas('voucher', fn($q) => $q->where('status', 'approved'))
            ->selectRaw('COALESCE(SUM(credit - debit), 0) as net')
            ->value('net');

        $netPosition = $totalAssets - $totalExpenses;

        $pendingApprovalsCount = Voucher::where('status', 'pending_approval')->count();
        $totalPostedVouchers = Voucher::where('status', 'approved')->count();

        // 2. Individual Bank & Liquid Accounts Breakdown (Leaf accounts under Cash/Bank)
        $liquidAccounts = Account::where('type', 'asset')
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->where(function ($q) {
                $q->where('description', 'like', '%bank%')
                  ->orWhere('description', 'like', '%cash%')
                  ->orWhere('name', 'like', '%bank%')
                  ->orWhere('name', 'like', '%pesa%')
                  ->orWhere('name', 'like', '%cash%');
            })
            ->get()
            ->map(function ($acc) use ($asOfDate) {
                $totals = LedgerEntry::where('account_id', $acc->id)
                    ->whereHas('voucher', fn($q) => $q->where('status', 'approved')->where('transaction_date', '<=', $asOfDate))
                    ->selectRaw('COALESCE(SUM(debit - credit), 0) as balance')
                    ->first();
                $acc->current_balance = (float) ($totals->balance ?? 0);
                return $acc;
            })
            ->filter(fn($acc) => $acc->current_balance != 0 || in_array($acc->code, ['1110', '1120', '1130', '100000']));

        // 3. 6-Month Income vs Expense Trend for Visual Chart
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $year = $monthDate->year;
            $month = $monthDate->month;

            $months[] = $monthLabel;

            $inc = (float) LedgerEntry::whereHas('account', fn($q) => $q->where('type', 'revenue'))
                ->whereHas('voucher', fn($q) => $q->where('status', 'approved')
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month))
                ->selectRaw('COALESCE(SUM(credit - debit), 0) as net')
                ->value('net');

            $exp = (float) LedgerEntry::whereHas('account', fn($q) => $q->where('type', 'expense'))
                ->whereHas('voucher', fn($q) => $q->where('status', 'approved')
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month))
                ->selectRaw('COALESCE(SUM(debit - credit), 0) as net')
                ->value('net');

            $incomeData[] = $inc;
            $expenseData[] = $exp;
        }

        // 4. Feed of Recent Vouchers & Approvals
        $recentVouchers = Voucher::with(['program', 'creator'])
            ->latest('transaction_date')
            ->take(5)
            ->get();

        $pendingVouchers = Voucher::with(['program', 'creator'])
            ->where('status', 'pending_approval')
            ->latest('transaction_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalAssets',
            'totalExpenses',
            'totalIncome',
            'netPosition',
            'pendingApprovalsCount',
            'totalPostedVouchers',
            'liquidAccounts',
            'months',
            'incomeData',
            'expenseData',
            'recentVouchers',
            'pendingVouchers'
        ));
    }

    public function approvalsQueue()
    {
        $vouchers = Voucher::with(['program', 'creator', 'ledgerEntries.account'])
            ->where('status', 'pending_approval')
            ->latest('transaction_date')
            ->paginate(15);

        return view('admin.approvals', compact('vouchers'));
    }

    public function chartOfAccounts(Request $request)
    {
        $showInactive = $request->boolean('show_inactive', false);

        $accounts = Account::with(['children', 'parent'])
            ->when(!$showInactive, fn($q) => $q->where('is_active', true))
            ->orderBy('code')
            ->get();

        $parentAccounts = Account::orderBy('code')->get();

        return view('admin.accounts', compact('accounts', 'showInactive', 'parentAccounts'));
    }
}
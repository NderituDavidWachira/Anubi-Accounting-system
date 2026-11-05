<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Program;
use App\Models\User;
use App\Models\Voucher;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // High-level summary metrics
        $pendingApprovalsCount = Voucher::where('status', 'pending_approval')->count();
        $totalPostedVouchers = Voucher::where('status', 'approved')->count();
        $totalPrograms = Program::where('is_active', true)->count();
        $totalStaff = User::where('role', 'staff')->count();

        // Financial totals (all approved ledger postings)
        $totalDebits = LedgerEntry::whereHas('voucher', fn($q) => $q->where('status', 'approved'))->sum('debit');
        $totalCredits = LedgerEntry::whereHas('voucher', fn($q) => $q->where('status', 'approved'))->sum('credit');

        // Recent activity feed across the entire organization
        $recentVouchers = Voucher::with(['program', 'creator', 'approver'])
            ->latest()
            ->take(8)
            ->get();

        // Pending vouchers queue needing action
        $pendingVouchers = Voucher::with(['program', 'creator'])
            ->where('status', 'pending_approval')
            ->oldest('transaction_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'pendingApprovalsCount',
            'totalPostedVouchers',
            'totalPrograms',
            'totalStaff',
            'totalDebits',
            'totalCredits',
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

    public function chartOfAccounts()
    {
        $accounts = Account::orderBy('code')->paginate(25);
        return view('admin.accounts', compact('accounts'));
    }
}
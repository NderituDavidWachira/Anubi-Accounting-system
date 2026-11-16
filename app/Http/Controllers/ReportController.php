<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Date column used on the vouchers table
    private string $dateColumn = 'created_at';

    /**
     * Report Hub: Central page to generate any report with From-To date filters
     */
    public function hub()
    {
        $accounts = Account::orderBy('code')->get();
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo = now()->toDateString();

        return view('reports.hub', compact('accounts', 'defaultFrom', 'defaultTo'));
    }

    /**
     * Existing Screen View: General Ledger / Account QuickReport
     */
    public function generalLedger(Request $request)
    {
        $accounts = Account::orderBy('code')->get();
        $selectedAccountId = $request->input('account_id');
        $startDate = $request->input('start_date', $request->input('from_date'));
        $endDate = $request->input('end_date', $request->input('to_date', now()->toDateString()));
        $col = $this->dateColumn;

        $account = $selectedAccountId ? Account::find($selectedAccountId) : null;

        $entries = LedgerEntry::with(['voucher', 'account'])
            ->when($selectedAccountId, fn($q) => $q->where('account_id', $selectedAccountId))
            ->whereHas('voucher', function ($q) use ($startDate, $endDate, $col) {
                $q->where('status', 'approved');
                if ($startDate) {
                    $q->whereDate($col, '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate($col, '<=', $endDate);
                }
            })
            ->get()
            ->sortBy(fn($entry) => $entry->voucher->{$col});

        return view('reports.general-ledger', [
            'accounts' => $accounts,
            'account' => $account,
            'entries' => $entries,
            'accountId' => $selectedAccountId,
            'selectedAccountId' => $selectedAccountId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Existing Screen View: Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', $request->input('to_date', now()->toDateString()));
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $col = $this->dateColumn;

        $accounts = Account::with(['ledgerEntries' => function ($query) use ($asOfDate, $col) {
            $query->whereHas('voucher', function ($v) use ($asOfDate, $col) {
                $v->where('status', 'approved')
                  ->whereDate($col, '<=', $asOfDate);
            });
        }])->orderBy('code')->get();

        $totalDebits = 0;
        $totalCredits = 0;
        $entries = [];

        foreach ($accounts as $account) {
            $debit = $account->ledgerEntries->sum('debit');
            $credit = $account->ledgerEntries->sum('credit');
            $net = $debit - $credit;

            $debitBal = $net > 0 ? $net : 0;
            $creditBal = $net < 0 ? abs($net) : 0;

            $account->debit_balance = $debitBal;
            $account->credit_balance = $creditBal;

            if ($debitBal > 0 || $creditBal > 0) {
                $entries[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'debit' => $debitBal,
                    'credit' => $creditBal,
                ];
            }

            $totalDebits += $debitBal;
            $totalCredits += $creditBal;
        }

        $trialBalance = [
            'entries' => $entries,
            'total_debit' => $totalDebits,
            'total_credit' => $totalCredits,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];

        return view('reports.trial-balance', [
            'accounts' => $accounts,
            'trialBalance' => $trialBalance,
            'asOfDate' => $asOfDate,
            'fromDate' => $fromDate,
            'toDate' => $asOfDate,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
        ]);
    }

    /**
     * Existing Screen View: Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', $request->input('to_date', now()->toDateString()));
        $col = $this->dateColumn;

        $accounts = Account::with(['ledgerEntries' => function ($query) use ($asOfDate, $col) {
            $query->whereHas('voucher', function ($v) use ($asOfDate, $col) {
                $v->where('status', 'approved')
                  ->whereDate($col, '<=', $asOfDate);
            });
        }])->orderBy('code')->get();

        $assets = collect();
        $liabilities = collect();
        $equity = collect();
        $revenues = collect();
        $expenses = collect();
        $balances = [];

        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;
        $totalRevenue = 0;
        $totalExpenses = 0;

        foreach ($accounts as $account) {
            $debit = $account->ledgerEntries->sum('debit');
            $credit = $account->ledgerEntries->sum('credit');
            $type = strtolower($account->type);

            if ($type === 'asset') {
                $bal = $debit - $credit;
                $account->balance = $bal;
                $balances[$account->id] = $bal;
                $assets->push($account);
                $totalAssets += $bal;
            } elseif ($type === 'liability') {
                $bal = $credit - $debit;
                $account->balance = $bal;
                $balances[$account->id] = $bal;
                $liabilities->push($account);
                $totalLiabilities += $bal;
            } elseif ($type === 'equity') {
                $bal = $credit - $debit;
                $account->balance = $bal;
                $balances[$account->id] = $bal;
                $equity->push($account);
                $totalEquity += $bal;
            } elseif (in_array($type, ['revenue', 'income'])) {
                $bal = $credit - $debit;
                $account->balance = $bal;
                $balances[$account->id] = $bal;
                $revenues->push($account);
                $totalRevenue += $bal;
            } elseif (in_array($type, ['expense', 'expenses'])) {
                $bal = $debit - $credit;
                $account->balance = $bal;
                $balances[$account->id] = $bal;
                $expenses->push($account);
                $totalExpenses += $bal;
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;
        $netOperatingPosition = $totalAssets - $totalExpenses;
        $netAvailableBalance = $totalAssets - $totalLiabilities;
        $retainedEarnings = $netIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity + $netIncome;

        return view('reports.balance-sheet', [
            'accounts' => $accounts,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'revenues' => $revenues,
            'income' => $revenues,
            'expenses' => $expenses,
            'assetAccounts' => $assets,
            'liabilityAccounts' => $liabilities,
            'equityAccounts' => $equity,
            'balances' => $balances,
            'asOfDate' => $asOfDate,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalRevenue' => $totalRevenue,
            'totalIncome' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $netIncome,
            'netOperatingPosition' => $netOperatingPosition,
            'netAvailableBalance' => $netAvailableBalance,
            'retainedEarnings' => $retainedEarnings,
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
        ]);
    }

    /**
     * Shared P&L Calculation (Excludes Opening Balances, Assets, Liabilities, and Equity)
     */
    private function getProfitLossData(string $fromDate, string $toDate): array
    {
        $col = $this->dateColumn;

        $revenueAccounts = Account::whereIn('type', ['revenue', 'income'])
            ->with(['ledgerEntries' => function ($query) use ($fromDate, $toDate, $col) {
                $query->whereHas('voucher', function ($v) use ($fromDate, $toDate, $col) {
                    $v->where('status', 'approved')
                      ->where('type', '!=', 'opening')
                      ->whereDate($col, '>=', $fromDate)
                      ->whereDate($col, '<=', $toDate);
                });
            }])->get();

        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $amount = $account->ledgerEntries->sum('credit') - $account->ledgerEntries->sum('debit');
            if ($amount != 0) {
                $revenues[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            }
        }

        $expenseAccounts = Account::whereIn('type', ['expense', 'expenses'])
            ->with(['ledgerEntries' => function ($query) use ($fromDate, $toDate, $col) {
                $query->whereHas('voucher', function ($v) use ($fromDate, $toDate, $col) {
                    $v->where('status', 'approved')
                      ->where('type', '!=', 'opening')
                      ->whereDate($col, '>=', $fromDate)
                      ->whereDate($col, '<=', $toDate);
                });
            }])->get();

        $expenses = [];
        $totalExpenses = 0;

        foreach ($expenseAccounts as $account) {
            $amount = $account->ledgerEntries->sum('debit') - $account->ledgerEntries->sum('credit');
            if ($amount != 0) {
                $expenses[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalExpenses += $amount;
            }
        }

        $netProfitLoss = $totalRevenue - $totalExpenses;

        return compact('revenues', 'expenses', 'totalRevenue', 'totalExpenses', 'netProfitLoss', 'fromDate', 'toDate');
    }

    /**
     * Profit & Loss Screen View
     */
    public function profitLoss(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $data = $this->getProfitLossData($fromDate, $toDate);

        return view('reports.profit-loss', $data);
    }

    /**
     * Target-Blank Clean Print / PDF View for Profit & Loss
     */
    public function profitLossPrint(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $data = $this->getProfitLossData($fromDate, $toDate);

        return view('reports.print-profit-loss', $data);
    }

    /**
     * Target-Blank Clean Print / PDF View for General Ledger
     */
    public function generalLedgerPrint(Request $request)
    {
        $fromDate = $request->input('from_date', $request->input('start_date', now()->startOfMonth()->toDateString()));
        $toDate = $request->input('to_date', $request->input('end_date', now()->toDateString()));
        $accountId = $request->input('account_id');
        $col = $this->dateColumn;

        $account = $accountId ? Account::find($accountId) : null;

        $entries = LedgerEntry::with(['voucher', 'account'])
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->whereHas('voucher', function ($q) use ($fromDate, $toDate, $col) {
                $q->where('status', 'approved')
                  ->whereDate($col, '>=', $fromDate)
                  ->whereDate($col, '<=', $toDate);
            })
            ->get()
            ->sortBy(fn($entry) => $entry->voucher->{$col});

        return view('reports.print-general-ledger', compact('entries', 'account', 'fromDate', 'toDate'));
    }

    /**
     * Target-Blank Clean Print / PDF View for Trial Balance
     */
    public function trialBalancePrint(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $col = $this->dateColumn;

        $accounts = Account::with(['ledgerEntries' => function ($query) use ($fromDate, $toDate, $col) {
            $query->whereHas('voucher', function ($v) use ($fromDate, $toDate, $col) {
                $v->where('status', 'approved')
                  ->whereDate($col, '>=', $fromDate)
                  ->whereDate($col, '<=', $toDate);
            });
        }])->orderBy('code')->get();

        return view('reports.print-trial-balance', compact('accounts', 'fromDate', 'toDate'));
    }

    /**
     * Target-Blank Clean Print / PDF View for Balance Sheet
     */
    public function balanceSheetPrint(Request $request)
    {
        $toDate = $request->input('to_date', $request->input('as_of_date', now()->toDateString()));
        $col = $this->dateColumn;

        $accounts = Account::with(['ledgerEntries' => function ($query) use ($toDate, $col) {
            $query->whereHas('voucher', function ($v) use ($toDate, $col) {
                $v->where('status', 'approved')
                  ->whereDate($col, '<=', $toDate);
            });
        }])->orderBy('code')->get();

        return view('reports.print-balance-sheet', compact('accounts', 'toDate'));
    }
}
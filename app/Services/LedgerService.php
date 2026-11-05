<?php

namespace App\Services;

use App\Models\Account;
use App\Models\LedgerEntry;
use Illuminate\Support\Collection;

class LedgerService
{
    /**
     * Get account card entries with running balances.
     */
    public function getAccountLedger(int $accountId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $account = Account::findOrFail($accountId);

        $query = LedgerEntry::with('voucher')
            ->where('account_id', $accountId)
            ->whereHas('voucher', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'approved');
                if ($startDate) $q->where('transaction_date', '>=', $startDate);
                if ($endDate) $q->where('transaction_date', '<=', $endDate);
            })
            ->join('vouchers', 'ledger_entries.voucher_id', '=', 'vouchers.id')
            ->orderBy('vouchers.transaction_date', 'asc')
            ->orderBy('vouchers.id', 'asc')
            ->select('ledger_entries.*');

        $entries = $query->get();

        $runningBalance = 0.00;
        $isDebitNormal = in_array($account->type, ['asset', 'expense']);

        return $entries->map(function ($entry) use (&$runningBalance, $isDebitNormal) {
            $change = $isDebitNormal
                ? ($entry->debit - $entry->credit)
                : ($entry->credit - $entry->debit);

            $runningBalance += $change;
            $entry->running_balance = round($runningBalance, 2);

            return $entry;
        });
    }

    /**
     * Generate Trial Balance.
     */
    public function generateTrialBalance(?string $asOfDate = null): array
    {
        $accounts = Account::where('is_active', true)->get();

        $report = [];
        $grandTotalDebit = 0.00;
        $grandTotalCredit = 0.00;

        foreach ($accounts as $account) {
            $totals = LedgerEntry::where('account_id', $account->id)
                ->whereHas('voucher', function ($q) use ($asOfDate) {
                    $q->where('status', 'approved');
                    if ($asOfDate) $q->where('transaction_date', '<=', $asOfDate);
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $totalDebit = (float) $totals->total_debit;
            $totalCredit = (float) $totals->total_credit;

            if (in_array($account->type, ['asset', 'expense'])) {
                $netDebit = max(0, $totalDebit - $totalCredit);
                $netCredit = max(0, $totalCredit - $totalDebit);
            } else {
                $netCredit = max(0, $totalCredit - $totalDebit);
                $netDebit = max(0, $totalDebit - $totalCredit);
            }

            if ($netDebit > 0 || $netCredit > 0) {
                $report[] = [
                    'code'   => $account->code,
                    'name'   => $account->name,
                    'type'   => $account->type,
                    'debit'  => $netDebit,
                    'credit' => $netCredit,
                ];

                $grandTotalDebit += $netDebit;
                $grandTotalCredit += $netCredit;
            }
        }

        return [
            'entries'      => $report,
            'total_debit'  => round($grandTotalDebit, 2),
            'total_credit' => round($grandTotalCredit, 2),
            'is_balanced'  => bccomp($grandTotalDebit, $grandTotalCredit, 2) === 0,
        ];
    }
}
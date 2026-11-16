<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinancialLedgerService
{
    /**
     * Create and validate a voucher transaction with its double-entry items.
     */
    public function createVoucher(array $data, array $lines, ?User $creator = null): Voucher
    {
        return DB::transaction(function () use ($data, $lines, $creator) {
            $totalDebit = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            // Enforce double-entry balance check
            if (bccomp((string)$totalDebit, (string)$totalCredit, 2) !== 0) {
                throw new InvalidArgumentException("Debit and Credit totals must balance. Debits: {$totalDebit}, Credits: {$totalCredit}");
            }

            $isAdmin = $creator && $creator->isAdmin();

            $voucher = Voucher::create([
                'voucher_number'   => $this->generateVoucherNumber($data['type']),
                'type'             => $data['type'],
                'program_id'       => !empty($data['program_id']) ? $data['program_id'] : null,
                'transaction_date' => $data['transaction_date'],
                'payee_or_source'  => $data['payee_or_source'],
                'narration'        => $data['narration'],
                'status'           => $isAdmin ? 'approved' : 'pending_approval',
                'created_by'       => $creator ? $creator->id : null,
                'approved_by'      => $isAdmin ? $creator->id : null,
                'approved_at'      => $isAdmin ? now() : null,
            ]);

            foreach ($lines as $line) {
                $debit = (float)($line['debit'] ?? 0);
                $credit = (float)($line['credit'] ?? 0);

                // Skip rows where both are zero
                if ($debit == 0 && $credit == 0) {
                    continue;
                }

                $voucher->ledgerEntries()->create([
                    'account_id' => $line['account_id'],
                    'debit'      => $debit,
                    'credit'     => $credit,
                    'line_memo'  => $line['memo'] ?? null,
                ]);
            }

            return $voucher;
        });
    }

    /**
     * Generate sequential institutional voucher numbers (e.g. PV-2026-00001).
     */
    private function generateVoucherNumber(string $type): string
    {
        $prefix = match ($type) {
            'payment' => 'PV',
            'receipt' => 'RV',
            default   => 'JV',
        };

        $year = date('Y');
        $count = Voucher::whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $count);
    }
}
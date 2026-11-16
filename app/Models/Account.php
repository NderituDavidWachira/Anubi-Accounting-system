<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'description',
        'is_active',
    ];

    /**
     * Parent group account (e.g., 1000 Assets).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Sub-accounts belonging to this account.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Ledger line entries posted to this account.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Compute current balance based on double-entry rules.
     */
    public function getBalanceAttribute(): float
    {
        $debitTotal = (float) $this->ledgerEntries()
            ->whereHas('voucher', fn($q) => $q->where('status', 'approved'))
            ->sum('debit');

        $creditTotal = (float) $this->ledgerEntries()
            ->whereHas('voucher', fn($q) => $q->where('status', 'approved'))
            ->sum('credit');

        // Assets and Expenses are debit-normal; Liabilities, Equity, and Revenue are credit-normal
        return in_array($this->type, ['asset', 'expense'])
            ? ($debitTotal - $creditTotal)
            : ($creditTotal - $debitTotal);
    }
}
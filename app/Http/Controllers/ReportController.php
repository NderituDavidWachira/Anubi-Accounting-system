<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\LedgerService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function generalLedger(Request $request)
    {
        $accounts = Account::orderBy('code')->get();
        $selectedAccountId = $request->input('account_id');
        $entries = collect();

        if ($selectedAccountId) {
            $entries = $this->ledgerService->getAccountLedger(
                $selectedAccountId,
                $request->input('start_date'),
                $request->input('end_date')
            );
        }

        return view('reports.general-ledger', compact('accounts', 'entries', 'selectedAccountId'));
    }

    public function trialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $trialBalance = $this->ledgerService->generateTrialBalance($asOfDate);

        return view('reports.trial-balance', compact('trialBalance', 'asOfDate'));
    }
}
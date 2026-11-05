<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Program;
use App\Models\Voucher;
use App\Services\FinancialLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = Voucher::with(['program', 'creator'])->latest('transaction_date')->paginate(20);
        return view('vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $programs = Program::where('is_active', true)->get();
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('vouchers.create', compact('programs', 'accounts'));
    }

    public function store(Request $request, FinancialLedgerService $ledgerService)
    {
        $validated = $request->validate([
            'type'             => 'required|in:payment,receipt,journal',
            'transaction_date' => 'required|date',
            'program_id'       => 'nullable|exists:programs,id',
            'payee_or_source'  => 'required|string|max:255',
            'narration'        => 'required|string|max:1000',
            'lines'            => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit'      => 'required|numeric|min:0',
            'lines.*.credit'     => 'required|numeric|min:0',
            'lines.*.memo'       => 'nullable|string|max:255',
        ]);

        $voucher = $ledgerService->createVoucher($validated, $validated['lines'], $request->user());

        return redirect()->route('vouchers.show', $voucher)
            ->with('success', "Voucher {$voucher->voucher_number} created successfully.");
    }

    public function show(Voucher $voucher)
    {
        $voucher->load(['ledgerEntries.account', 'program.department', 'creator', 'approver']);
        return view('vouchers.show', compact('voucher'));
    }

    public function approve(Request $request, Voucher $voucher)
    {
        $voucher->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('vouchers.show', $voucher)
            ->with('success', "Voucher {$voucher->voucher_number} approved and posted to General Ledger.");
    }
}
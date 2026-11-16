<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Program;
use App\Models\Voucher;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::with(['program', 'creator', 'ledgerEntries.account'])
            ->latest('transaction_date')
            ->paginate(15);

        return view('vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $programs = Program::where('is_active', true)->orderBy('name')->get();

        $year = now()->format('Y');
        $nextSeq = Voucher::whereYear('created_at', $year)->count() + 1;
        $prospectiveNumber = sprintf('PV-%s-%05d', $year, $nextSeq);

        return view('vouchers.create', compact('accounts', 'programs', 'prospectiveNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'             => 'required|in:payment,receipt,journal',
            'transaction_date' => 'required|date',
            'program_id'       => 'nullable',
            'payee'            => 'nullable|string|max:255',
            'narration'        => 'required|string|max:500',
            'entries'          => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:accounts,id',
            'entries.*.memo'       => 'nullable|string|max:255',
            'entries.*.debit'      => 'nullable|numeric|min:0',
            'entries.*.credit'     => 'nullable|numeric|min:0',
        ]);

        $programId = $request->input('program_id');
        if (empty($programId) || $programId === 'other') {
            $programId = null;
        } else {
            $programId = (int) $programId;
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($request->input('entries', []) as $entry) {
            $totalDebit += (float) ($entry['debit'] ?? 0);
            $totalCredit += (float) ($entry['credit'] ?? 0);
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2) || $totalDebit <= 0) {
            return back()->withInput()->withErrors([
                'double_entry' => 'Double-entry balance check failed. Debits (KSh ' . number_format($totalDebit, 2) . ') must equal Credits (KSh ' . number_format($totalCredit, 2) . ').'
            ]);
        }

        DB::transaction(function () use ($request, $programId) {
            $prefix = match ($request->input('type')) {
                'payment' => 'PV',
                'receipt' => 'RV',
                default   => 'JV',
            };
            $year = now()->format('Y');
            $count = Voucher::where('type', $request->input('type'))->whereYear('created_at', $year)->count() + 1;
            $voucherNumber = sprintf('%s-%s-%05d', $prefix, $year, $count);

            $voucher = Voucher::create([
                'voucher_number'   => $voucherNumber,
                'type'             => $request->input('type'),
                'transaction_date' => $request->input('transaction_date'),
                'program_id'       => $programId,
                'payee'            => $request->input('payee'),
                'narration'        => $request->input('narration'),
                'status'           => 'approved',
                'created_by'       => Auth::id() ?? 1,
            ]);

            foreach ($request->input('entries') as $entryData) {
                $debit = (float) ($entryData['debit'] ?? 0);
                $credit = (float) ($entryData['credit'] ?? 0);

                if ($debit > 0 || $credit > 0) {
                    LedgerEntry::create([
                        'voucher_id' => $voucher->id,
                        'account_id' => $entryData['account_id'],
                        'line_memo'  => $entryData['memo'] ?? null,
                        'debit'      => $debit,
                        'credit'     => $credit,
                    ]);
                }
            }
        });

        return redirect()->route('vouchers.index')->with('success', 'Voucher committed to General Ledger.');
    }

    public function show($id)
    {
        $voucher = Voucher::with(['program', 'creator', 'ledgerEntries.account'])->findOrFail($id);
        return view('vouchers.show', compact('voucher'));
    }
}
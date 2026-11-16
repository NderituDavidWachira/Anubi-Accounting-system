@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create New Financial Voucher</h1>
    </div>

    @if($errors->has('double_entry'))
        <div class="mb-5 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded">
            {{ $errors->first('double_entry') }}
        </div>
    @endif

    <form action="{{ route('vouchers.store') }}" method="POST" id="voucherForm" class="space-y-6">
        @csrf

        <!-- Row 1: Type, Date, Program / Project -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Voucher Type <span class="text-red-500 font-bold">*</span>
                </label>
                <select name="type" id="voucherType" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-slate-500">
                    <option value="journal" {{ old('type') == 'journal' ? 'selected' : '' }}>Journal Voucher</option>
                    <option value="payment" {{ old('type') == 'payment' ? 'selected' : '' }}>Payment Voucher</option>
                    <option value="receipt" {{ old('type') == 'receipt' ? 'selected' : '' }}>Receipt Voucher</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Transaction Date <span class="text-red-500 font-bold">*</span>
                </label>
                <div class="relative">
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-slate-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Program / Project</label>
                <select name="program_id" id="programSelect"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-slate-500">
                    <option value="">-- General Administrative --</option>
                    @if(isset($programs) && $programs->isNotEmpty())
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ old('program_id') == $prog->id ? 'selected' : '' }}>
                                {{ $prog->code ? $prog->code . ' - ' : '' }}{{ $prog->name }}
                            </option>
                        @endforeach
                    @endif
                    <option value="other" {{ old('program_id') === 'other' ? 'selected' : '' }}>Other / Unassigned Project</option>
                </select>
            </div>
        </div>

        <!-- Row 2: Payee, Narration -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payee / Received From</label>
                <input type="text" name="payee" value="{{ old('payee') }}" placeholder="Vendor, Employee, or Donor"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-1 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Narration / Description <span class="text-red-500 font-bold">*</span>
                </label>
                <input type="text" name="narration" value="{{ old('narration') }}" required placeholder="Reason for transaction"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-1 focus:ring-slate-500">
            </div>
        </div>

        <!-- Section Title: LEDGER DISTRIBUTION (DOUBLE ENTRY) -->
        <div class="pt-2">
            <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Ledger Distribution (Double Entry)</h2>
            
            <div class="border border-gray-200 rounded overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-600 text-xs">
                                Account <span class="text-red-500 font-bold">*</span>
                            </th>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-600 text-xs">Memo</th>
                            <th class="px-4 py-2.5 text-right font-semibold text-gray-600 text-xs w-36">Debit</th>
                            <th class="px-4 py-2.5 text-right font-semibold text-gray-600 text-xs w-36">Credit</th>
                            <th class="px-2 py-2.5 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white" id="ledgerRows">
                        <!-- Row 0 -->
                        <tr class="ledger-row">
                            <td class="px-4 py-2">
                                <select name="entries[0][account_id]" required class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs bg-white focus:outline-none">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="entries[0][memo]" placeholder="Memo" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs focus:outline-none">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" min="0" name="entries[0][debit]" oninput="calculateTotals()" placeholder="0.00" class="debit-input w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs text-right font-mono focus:outline-none">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" min="0" name="entries[0][credit]" oninput="calculateTotals()" placeholder="0.00" class="credit-input w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs text-right font-mono focus:outline-none">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button type="button" onclick="removeRow(this)" class="text-gray-300 hover:text-red-500 font-bold text-base">&times;</button>
                            </td>
                        </tr>

                        <!-- Row 1 -->
                        <tr class="ledger-row">
                            <td class="px-4 py-2">
                                <select name="entries[1][account_id]" required class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs bg-white focus:outline-none">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="entries[1][memo]" placeholder="Memo" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs focus:outline-none">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" min="0" name="entries[1][debit]" oninput="calculateTotals()" placeholder="0.00" class="debit-input w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs text-right font-mono focus:outline-none">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" min="0" name="entries[1][credit]" oninput="calculateTotals()" placeholder="0.00" class="credit-input w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs text-right font-mono focus:outline-none">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button type="button" onclick="removeRow(this)" class="text-gray-300 hover:text-red-500 font-bold text-base">&times;</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-gray-200 bg-white">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right font-bold text-gray-700 text-xs">Totals:</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 text-xs" id="totalDebitDisplay">0.00</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 text-xs" id="totalCreditDisplay">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="flex justify-between items-center pt-2">
            <button type="button" onclick="addRow()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-4 py-2 rounded transition">
                + Add Row
            </button>

            <div class="flex items-center space-x-4">
                <span id="balanceIndicator" class="text-sm font-semibold text-emerald-600">
                    Balanced
                </span>
                <button type="submit" id="saveButton" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2 rounded transition shadow-sm">
                    Save Voucher
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let rowIndex = 2;
const accountsData = @json($accounts);

function calculateTotals() {
    let debits = 0;
    let credits = 0;

    document.querySelectorAll('.debit-input').forEach(input => {
        debits += parseFloat(input.value) || 0;
    });

    document.querySelectorAll('.credit-input').forEach(input => {
        credits += parseFloat(input.value) || 0;
    });

    document.getElementById('totalDebitDisplay').textContent = debits.toFixed(2);
    document.getElementById('totalCreditDisplay').textContent = credits.toFixed(2);

    const indicator = document.getElementById('balanceIndicator');
    const saveBtn = document.getElementById('saveButton');
    const diff = Math.abs(debits - credits);

    if (debits > 0 && diff < 0.001) {
        indicator.textContent = 'Balanced';
        indicator.className = 'text-sm font-semibold text-emerald-600';
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        indicator.textContent = 'Not Balanced';
        indicator.className = 'text-sm font-semibold text-red-600';
    }
}

function addRow() {
    const tbody = document.getElementById('ledgerRows');
    const tr = document.createElement('tr');
    tr.className = 'ledger-row';

    let optionsHtml = '<option value="">Select Account</option>';
    accountsData.forEach(acc => {
        const type = acc.type.charAt(0).toUpperCase() + acc.type.slice(1);
        optionsHtml += `<option value="${acc.id}">${acc.code} - ${acc.name} (${type})</option>`;
    });

    tr.innerHTML = `
        <td class="px-4 py-2">
            <select name="entries[${rowIndex}][account_id]" required class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs bg-white focus:outline-none">
                ${optionsHtml}
            </select>
        </td>
        <td class="px-4 py-2">
            <input type="text" name="entries[${rowIndex}][memo]" placeholder="Memo" class="w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs focus:outline-none">
        </td>
        <td class="px-4 py-2">
            <input type="number" step="0.01" min="0" name="entries[${rowIndex}][debit]" oninput="calculateTotals()" placeholder="0.00" class="debit-input w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs text-right font-mono focus:outline-none">
        </td>
        <td class="px-4 py-2">
            <input type="number" step="0.01" min="0" name="entries[${rowIndex}][credit]" oninput="calculateTotals()" placeholder="0.00" class="credit-input w-full border border-gray-300 rounded px-2.5 py-1.5 text-xs text-right font-mono focus:outline-none">
        </td>
        <td class="px-2 py-2 text-center">
            <button type="button" onclick="removeRow(this)" class="text-gray-300 hover:text-red-500 font-bold text-base">&times;</button>
        </td>
    `;

    tbody.appendChild(tr);
    rowIndex++;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.ledger-row');
    if (rows.length > 2) {
        btn.closest('tr').remove();
        calculateTotals();
    } else {
        alert('A voucher requires at least two ledger entry rows.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
@endsection
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Create New Financial Voucher</h2>

    <form action="{{ route('vouchers.store') }}" method="POST" id="voucherForm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Voucher Type</label>
                <select name="type" class="w-full border-gray-300 rounded-md border p-2 text-sm" required>
                    <option value="payment">Payment Voucher</option>
                    <option value="receipt">Receipt Voucher</option>
                    <option value="journal">Journal Voucher</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Transaction Date</label>
                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-md border p-2 text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Program / Project</label>
                <select name="program_id" class="w-full border-gray-300 rounded-md border p-2 text-sm">
                    <option value="">-- General Administrative --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Payee / Received From</label>
                <input type="text" name="payee_or_source" placeholder="Vendor, Employee, or Donor" class="w-full border-gray-300 rounded-md border p-2 text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Narration / Description</label>
                <input type="text" name="narration" placeholder="Reason for transaction" class="w-full border-gray-300 rounded-md border p-2 text-sm" required>
            </div>
        </div>

        <!-- Ledger Entries Table -->
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Ledger Distribution (Double Entry)</h3>
        <table class="min-w-full divide-y divide-gray-200 border mb-4 text-sm" id="entriesTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 text-left">Account</th>
                    <th class="p-2 text-left">Memo</th>
                    <th class="p-2 text-right w-32">Debit</th>
                    <th class="p-2 text-right w-32">Credit</th>
                    <th class="p-2 text-center w-12"></th>
                </tr>
            </thead>
            <tbody id="lineItems">
                <tr>
                    <td class="p-2">
                        <select name="lines[0][account_id]" class="w-full border rounded p-1 text-xs" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2"><input type="text" name="lines[0][memo]" class="w-full border rounded p-1 text-xs" placeholder="Memo"></td>
                    <td class="p-2"><input type="number" step="0.01" name="lines[0][debit]" value="0.00" class="w-full border rounded p-1 text-xs text-right debit-input" oninput="calculateTotals()"></td>
                    <td class="p-2"><input type="number" step="0.01" name="lines[0][credit]" value="0.00" class="w-full border rounded p-1 text-xs text-right credit-input" oninput="calculateTotals()"></td>
                    <td class="p-2 text-center"></td>
                </tr>
                <tr>
                    <td class="p-2">
                        <select name="lines[1][account_id]" class="w-full border rounded p-1 text-xs" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="p-2"><input type="text" name="lines[1][memo]" class="w-full border rounded p-1 text-xs" placeholder="Memo"></td>
                    <td class="p-2"><input type="number" step="0.01" name="lines[1][debit]" value="0.00" class="w-full border rounded p-1 text-xs text-right debit-input" oninput="calculateTotals()"></td>
                    <td class="p-2"><input type="number" step="0.01" name="lines[1][credit]" value="0.00" class="w-full border rounded p-1 text-xs text-right credit-input" oninput="calculateTotals()"></td>
                    <td class="p-2 text-center"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="2" class="p-2 text-right">Totals:</td>
                    <td class="p-2 text-right" id="totalDebit">0.00</td>
                    <td class="p-2 text-right" id="totalCredit">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="flex justify-between items-center">
            <button type="button" onclick="addLine()" class="text-xs bg-gray-200 hover:bg-gray-300 font-semibold px-3 py-1 rounded">+ Add Row</button>
            <div class="space-x-2">
                <span id="balanceIndicator" class="text-xs font-bold text-emerald-600 mr-2">Balanced</span>
                <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded text-sm shadow">Save Voucher</button>
            </div>
        </div>
    </form>
</div>

<script>
let lineIndex = 2;
function addLine() {
    const tbody = document.getElementById('lineItems');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="p-2">
            <select name="lines[${lineIndex}][account_id]" class="w-full border rounded p-1 text-xs" required>
                <option value="">Select Account</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="p-2"><input type="text" name="lines[${lineIndex}][memo]" class="w-full border rounded p-1 text-xs" placeholder="Memo"></td>
        <td class="p-2"><input type="number" step="0.01" name="lines[${lineIndex}][debit]" value="0.00" class="w-full border rounded p-1 text-xs text-right debit-input" oninput="calculateTotals()"></td>
        <td class="p-2"><input type="number" step="0.01" name="lines[${lineIndex}][credit]" value="0.00" class="w-full border rounded p-1 text-xs text-right credit-input" oninput="calculateTotals()"></td>
        <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove(); calculateTotals();" class="text-red-500 font-bold">&times;</button></td>
    `;
    tbody.appendChild(row);
    lineIndex++;
}

function calculateTotals() {
    let debits = 0;
    let credits = 0;
    document.querySelectorAll('.debit-input').forEach(i => debits += parseFloat(i.value) || 0);
    document.querySelectorAll('.credit-input').forEach(i => credits += parseFloat(i.value) || 0);

    document.getElementById('totalDebit').textContent = debits.toFixed(2);
    document.getElementById('totalCredit').textContent = credits.toFixed(2);

    const indicator = document.getElementById('balanceIndicator');
    const submitBtn = document.getElementById('submitBtn');

    if (debits.toFixed(2) === credits.toFixed(2) && debits > 0) {
        indicator.textContent = 'Balanced';
        indicator.className = 'text-xs font-bold text-emerald-600 mr-2';
        submitBtn.disabled = false;
    } else {
        indicator.textContent = 'Unbalanced Difference: ' + Math.abs(debits - credits).toFixed(2);
        indicator.className = 'text-xs font-bold text-red-600 mr-2';
        submitBtn.disabled = true;
    }
}
</script>
@endsection
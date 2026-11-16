@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg border border-slate-200 shadow-sm">
    <!-- QuickBooks-style Top Action Bar -->
    <div class="px-6 py-5 border-b border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50 rounded-t-lg">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold text-slate-800">Chart of accounts</h1>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700 font-semibold">{{ $accounts->count() }} accounts</span>
            </div>
            <div class="mt-1 flex items-center space-x-3 text-xs">
                <a href="{{ route('admin.accounts', ['show_inactive' => !$showInactive]) }}" 
                   class="text-sky-700 hover:text-sky-900 font-medium hover:underline">
                    {{ $showInactive ? 'Hide inactive accounts' : 'Show inactive accounts' }}
                </a>
            </div>
        </div>

        <div class="flex items-center space-x-2.5 w-full md:w-auto">
            <input type="text" id="accountSearch" onkeyup="filterAccounts()" placeholder="Filter by name or code..." 
                   class="border border-slate-300 rounded px-3 py-1.5 text-xs w-full md:w-64 focus:outline-none focus:ring-1 focus:ring-slate-500 bg-white">
            <button type="button" onclick="openCreateModal()" 
                    class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold px-4 py-2 rounded transition whitespace-nowrap shadow-sm">
                + New account
            </button>
        </div>
    </div>

    <!-- QuickBooks Account Register Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-xs divide-y divide-slate-200" id="accountsTable">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                <tr>
                    <th class="py-3 px-5 w-24">Code</th>
                    <th class="py-3 px-5">Account Name</th>
                    <th class="py-3 px-5 w-36">Type</th>
                    <th class="py-3 px-5 w-32">Detail Type</th>
                    <th class="py-3 px-5 text-right w-36">Balance (KSh)</th>
                    <th class="py-3 px-5 text-center w-20">Status</th>
                    <th class="py-3 px-5 text-right w-24">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($accounts as $acc)
                    @php
                        $isHeader = $acc->children->isNotEmpty() || substr($acc->code, -2) === '00';
                        $isSubAccount = !is_null($acc->parent_id);
                        $balance = $acc->balance;
                    @endphp
                    <tr class="account-row {{ $isHeader ? 'bg-slate-50/80 font-bold text-slate-900' : 'hover:bg-slate-50' }}" 
                        data-name="{{ strtolower($acc->name) }}" data-code="{{ strtolower($acc->code) }}">
                        
                        <td class="py-2.5 px-5 font-mono {{ $isHeader ? 'text-slate-900' : 'text-slate-600' }}">
                            {{ $acc->code }}
                        </td>

                        <td class="py-2.5 px-5">
                            <div class="flex items-center space-x-2 {{ $isSubAccount ? 'pl-6' : '' }}">
                                @if($isSubAccount)
                                    <span class="text-slate-300 font-bold">&ndash;</span>
                                @endif
                                <span>{{ $acc->name }}</span>
                            </div>
                        </td>

                        <td class="py-2.5 px-5 capitalize text-slate-600">
                            {{ $acc->type === 'revenue' ? 'Income' : ucfirst($acc->type) }}
                        </td>

                        <td class="py-2.5 px-5">
                            @if(!empty($acc->description))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-cyan-100 text-cyan-800 border border-cyan-200">
                                    {{ $acc->description }}
                                </span>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>

                        <td class="py-2.5 px-5 text-right font-mono font-medium">
                            @if($balance != 0)
                                <span class="{{ $isHeader ? 'text-slate-900 font-bold' : 'text-slate-700' }}">
                                    {{ number_format(abs($balance), 2) }}
                                </span>
                            @else
                                <span class="text-slate-400">0.00</span>
                            @endif
                        </td>

                        <td class="py-2.5 px-5 text-center">
                            <span class="inline-block w-2 h-2 rounded-full {{ $acc->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $acc->is_active ? 'Active' : 'Inactive' }}"></span>
                        </td>

                        <td class="py-2.5 px-5 text-right">
                            <button type="button" 
                                    onclick='openEditModal(@json($acc))'
                                    class="text-sky-700 hover:text-sky-900 hover:underline font-semibold text-xs">
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No accounts recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-500">
        Group accounts (shaded) roll up sub-accounts underneath them. Normal balances: Assets & Expenses (Debit); Liabilities, Equity & Income (Credit).
    </div>
</div>

<!-- ================= CREATE ACCOUNT MODAL (SMART AUTO-SELECTION) ================= -->
<div id="createModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">New Account</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>

        <form action="{{ route('admin.accounts.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- 1. Primary Classification Group: Changing this drives the Sub-Account and Account Type automatically -->
            <div class="bg-slate-50 p-3.5 rounded border border-slate-200">
                <label class="block text-xs font-bold text-slate-700 mb-1">Parent Classification / Group *</label>
                <select id="create_parent_select" name="parent_id" onchange="handleParentSelection(this)" required 
                        class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs bg-white font-medium focus:ring-1 focus:ring-slate-500 focus:outline-none">
                    <option value="" data-type="" data-code="">-- Choose Category (e.g. Assets, Liabilities, Expense) --</option>
                    @foreach($parentAccounts as $p)
                        <option value="{{ $p->id }}" data-type="{{ $p->type }}" data-code="{{ $p->code }}" data-name="{{ $p->name }}">
                            {{ $p->code }} - {{ $p->name }} ({{ ucfirst($p->type) }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Selecting a group automatically populates the category type and generates the next code.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Generated Account Code *</label>
                    <input type="text" id="create_code" name="code" placeholder="e.g. 1140" required
                           class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs font-mono font-bold text-slate-800 focus:ring-1 focus:ring-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Account Type (Auto-Locked) *</label>
                    <select id="create_type" name="type" required 
                            class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs bg-slate-100 text-slate-700 font-semibold focus:outline-none cursor-not-allowed pointer-events-none">
                        <option value="asset">Asset</option>
                        <option value="liability">Liability</option>
                        <option value="equity">Equity</option>
                        <option value="revenue">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Account Name *</label>
                <input type="text" name="name" placeholder="e.g. Commercial Bank - Operations" required
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Detail Type / Badge Tag</label>
                <input type="text" id="create_description" name="description" placeholder="e.g. Cash or bank"
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none">
            </div>

            <!-- Opening Balance Entry -->
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded border border-slate-200">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Opening Balance (KSh)</label>
                    <input type="number" step="0.01" name="opening_balance" placeholder="0.00"
                           class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">As of Date</label>
                    <input type="date" name="as_of_date" value="{{ date('Y-m-d') }}"
                           class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs bg-white focus:outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 border border-slate-300 rounded text-xs font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-semibold shadow-sm">Save and close</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= EDIT ACCOUNT MODAL (STRUCTURALLY LOCKED FOR SAFETY) ================= -->
<div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Edit Account</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>

        <form id="editForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" id="edit_type_hidden" name="type">
            <input type="hidden" id="edit_parent_id_hidden" name="parent_id">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Account Code</label>
                    <input type="text" id="edit_code" name="code" readonly
                           class="w-full border border-slate-300 bg-slate-100 rounded px-3 py-1.5 text-xs font-mono font-bold text-slate-600 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Account Type (Fixed)</label>
                    <input type="text" id="edit_type_display" readonly
                           class="w-full border border-slate-300 bg-slate-100 rounded px-3 py-1.5 text-xs capitalize font-semibold text-slate-600 cursor-not-allowed">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Account Name *</label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Parent Classification</label>
                    <input type="text" id="edit_parent_name" readonly
                           class="w-full border border-slate-300 bg-slate-100 rounded px-3 py-1.5 text-xs text-slate-600 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Detail Type / Tag</label>
                    <input type="text" id="edit_description" name="description"
                           class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none">
                </div>
            </div>

            <!-- Opening Balance Modification -->
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded border border-slate-200">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Opening Balance (KSh)</label>
                    <input type="number" step="0.01" id="edit_balance" name="opening_balance" placeholder="0.00"
                           class="w-full border border-slate-300 rounded px-3 py-1.5 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">As of Date</label>
                    <input type="date" id="edit_date" name="as_of_date" value="{{ date('Y-m-d') }}"
                           class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs bg-white focus:outline-none">
                </div>
            </div>

            <div class="flex items-center pt-1">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <label for="edit_is_active" class="ml-2 text-xs font-medium text-slate-700">Account is Active</label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 rounded text-xs font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-semibold shadow-sm">Save changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Data store of registered codes for sequential generation
const allAccounts = @json($accounts);

function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

// Automatically bind Account Type & compute next Code on parent selection
function handleParentSelection(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const type = selectedOption.getAttribute('data-type');
    const parentCode = selectedOption.getAttribute('data-code');

    if (!type || !parentCode) {
        document.getElementById('create_code').value = '';
        return;
    }

    // 1. Lock Type automatically
    document.getElementById('create_type').value = type;

    // 2. Set default Detail Tag if under Cash and Bank
    const descInput = document.getElementById('create_description');
    if (parentCode.startsWith('11') || parentCode === '1000') {
        descInput.value = 'Cash or bank';
    } else {
        descInput.value = '';
    }

    // 3. Find highest existing child code and propose the next available number
    const baseCode = parseInt(parentCode);
    const existingChildCodes = allAccounts
        .map(a => parseInt(a.code))
        .filter(c => !isNaN(c) && c > baseCode && c < baseCode + 100);

    let nextCode = baseCode + 10;
    if (existingChildCodes.length > 0) {
        nextCode = Math.max(...existingChildCodes) + 10;
    }

    document.getElementById('create_code').value = nextCode;
}

function openEditModal(account) {
    const form = document.getElementById('editForm');
    form.action = `/admin/accounts/${account.id}`;

    document.getElementById('edit_code').value = account.code;
    document.getElementById('edit_name').value = account.name;
    document.getElementById('edit_type_hidden').value = account.type;
    document.getElementById('edit_type_display').value = account.type;
    document.getElementById('edit_parent_id_hidden').value = account.parent_id || '';
    document.getElementById('edit_parent_name').value = account.parent ? `${account.parent.code} - ${account.parent.name}` : 'Primary Category';
    document.getElementById('edit_description').value = account.description || '';
    document.getElementById('edit_is_active').checked = !!account.is_active;

    document.getElementById('edit_balance').value = account.balance ? Math.abs(account.balance) : '';

    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function filterAccounts() {
    const query = document.getElementById('accountSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.account-row');
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const code = row.getAttribute('data-code');
        if (name.includes(query) || code.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
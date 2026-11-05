@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Chart of Accounts</h1>
            <p class="text-sm text-slate-500">Master account categories and code assignments</p>
        </div>
    </div>

    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
            <tr>
                <th class="p-3 text-left">Code</th>
                <th class="p-3 text-left">Account Name</th>
                <th class="p-3 text-left">Category</th>
                <th class="p-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($accounts as $account)
                <tr class="hover:bg-slate-50">
                    <td class="p-3 font-mono font-bold text-slate-700">{{ $account->code }}</td>
                    <td class="p-3 font-medium">{{ $account->name }}</td>
                    <td class="p-3 uppercase text-xs font-semibold text-slate-400">{{ $account->type }}</td>
                    <td class="p-3 text-center">
                        <span class="px-2 py-0.5 rounded text-xs {{ $account->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-400">No accounts registered.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $accounts->links() }}</div>
</div>
@endsection
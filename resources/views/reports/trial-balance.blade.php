@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm border p-6">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Trial Balance</h1>
            <p class="text-sm text-gray-500">As of {{ $asOfDate }}</p>
        </div>
        <form method="GET" class="flex items-center space-x-2">
            <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="border rounded p-1 text-sm">
            <button type="submit" class="bg-slate-700 text-white px-3 py-1 rounded text-sm">Filter</button>
        </form>
    </div>

    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left font-semibold text-gray-600">Account Code</th>
                <th class="px-4 py-2 text-left font-semibold text-gray-600">Account Name</th>
                <th class="px-4 py-2 text-left font-semibold text-gray-600">Type</th>
                <th class="px-4 py-2 text-right font-semibold text-gray-600">Debit Balance</th>
                <th class="px-4 py-2 text-right font-semibold text-gray-600">Credit Balance</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($trialBalance['entries'] as $entry)
                <tr>
                    <td class="px-4 py-2 font-mono text-gray-600">{{ $entry['code'] }}</td>
                    <td class="px-4 py-2 font-medium">{{ $entry['name'] }}</td>
                    <td class="px-4 py-2 uppercase text-xs text-gray-400">{{ $entry['type'] }}</td>
                    <td class="px-4 py-2 text-right font-mono">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}</td>
                    <td class="px-4 py-2 text-right font-mono">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-gray-100 font-bold border-t-2">
                <td colspan="3" class="px-4 py-3 text-right">Totals:</td>
                <td class="px-4 py-3 text-right font-mono">{{ number_format($trialBalance['total_debit'], 2) }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ number_format($trialBalance['total_credit'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
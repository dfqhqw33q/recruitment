@extends('layouts.app')

@section('title', 'Offer Letters')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Offer Letters</h1>
            <p class="mt-1 text-sm text-gray-500">Manage job offers for candidates.</p>
        </div>
        @can('approve_offers')
        <a href="{{ route('recruitment.offers.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"><i class="fa-solid fa-plus mr-1"></i>Create Offer</a>
        @endcan
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="draft" @selected(request('status') == 'draft')>Draft</option>
            <option value="sent" @selected(request('status') == 'sent')>Sent</option>
            <option value="accepted" @selected(request('status') == 'accepted')>Accepted</option>
            <option value="rejected" @selected(request('status') == 'rejected')>Rejected</option>
            <option value="expired" @selected(request('status') == 'expired')>Expired</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offer Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($offers as $offer)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $offer->offer_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $offer->application->applicant->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $offer->jobPosting->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">₱{{ number_format($offer->salary, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($offer->start_date)->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $offer->status == 'accepted' ? 'bg-green-100 text-green-800' : ($offer->status == 'sent' ? 'bg-blue-100 text-blue-800' : ($offer->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">{{ ucfirst($offer->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('recruitment.offers.show', $offer) }}" class="text-indigo-600 hover:text-indigo-500">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">No offer letters found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $offers->links() }}</div>
</div>
@endsection
</content>

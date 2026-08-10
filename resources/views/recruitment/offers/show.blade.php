@extends('layouts.app')

@section('title', 'Offer Letter')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Offer Letter {{ $offer->offer_number }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $offer->application->applicant->full_name }} — {{ $offer->jobPosting->title }}</p>
        </div>
        <div class="flex space-x-2">
            @can('approve_offers')
            @if($offer->status === 'draft')
            <form method="POST" action="{{ route('recruitment.offers.send', $offer) }}">
                @csrf
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><i class="fa-solid fa-paper-plane mr-1"></i>Send Offer</button>
            </form>
            @endif
            @if($offer->status === 'sent')
            <form method="POST" action="{{ route('recruitment.offers.respond', $offer) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="accepted">
                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">Mark Accepted</button>
            </form>
            <form method="POST" action="{{ route('recruitment.offers.respond', $offer) }}" class="inline">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Mark Rejected</button>
            </form>
            @endif
            @endcan
        </div>
    </div>

    <!-- Offer Document -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 max-w-3xl">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Job Offer Letter</h2>
            <p class="text-sm text-gray-500">Offer No. {{ $offer->offer_number }}</p>
        </div>

        <div class="text-sm text-gray-700 space-y-4">
            <p>Dear <span class="font-semibold">{{ $offer->application->applicant->full_name }}</span>,</p>
            <p>We are pleased to offer you the position of <span class="font-semibold">{{ $offer->jobPosting->title }}</span> at our company. After careful consideration of your qualifications and performance during the interview process, we believe you will be a valuable addition to our team.</p>

            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Position</span><span class="font-medium">{{ $offer->jobPosting->title }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Monthly Salary</span><span class="font-medium">₱{{ number_format($offer->salary, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Employment Type</span><span class="font-medium capitalize">{{ str_replace('_', ' ', $offer->employment_type) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Start Date</span><span class="font-medium">{{ \Carbon\Carbon::parse($offer->start_date)->format('F d, Y') }}</span></div>
            </div>

            @if($offer->terms)
            <div>
                <h4 class="font-semibold text-gray-900">Terms & Conditions</h4>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $offer->terms }}</p>
            </div>
            @endif

            @if($offer->benefits)
            <div>
                <h4 class="font-semibold text-gray-900">Benefits</h4>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $offer->benefits }}</p>
            </div>
            @endif

            <p>We look forward to welcoming you to our team. Please review the terms and respond to this offer at your earliest convenience.</p>

            <div class="mt-8">
                <p>Sincerely,</p>
                <p class="font-semibold">{{ $offer->preparer->name }}</p>
                <p class="text-gray-500">Prepared: {{ \Carbon\Carbon::parse($offer->created_at)->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

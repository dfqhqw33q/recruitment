@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Recruitment Reports</h1>
        <p class="mt-1 text-sm text-gray-500">Generate and export recruitment reports.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Candidate Report -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mb-4">
                <i class="fa-solid fa-users text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Candidate Report</h3>
            <p class="text-sm text-gray-500 mt-1">Export all candidates with their application status and AI match scores.</p>
            <div class="mt-4 space-y-2">
                <button onclick="document.getElementById('candidateModal').classList.remove('hidden')" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Generate PDF</button>
                <a href="{{ route('reports.candidates', ['format' => 'csv']) }}" class="block w-full text-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Export CSV</a>
            </div>
        </div>

        <!-- Hiring Report -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="w-12 h-12 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-4">
                <i class="fa-solid fa-user-check text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Hiring Report</h3>
            <p class="text-sm text-gray-500 mt-1">Export all hired candidates details.</p>
            <div class="mt-4 space-y-2">
                <a href="{{ route('reports.hiring') }}" class="block w-full text-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">Generate PDF</a>
                <a href="{{ route('reports.hiring', ['format' => 'csv']) }}" class="block w-full text-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Export CSV</a>
            </div>
        </div>

        <!-- Recruitment Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="w-12 h-12 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                <i class="fa-solid fa-chart-bar text-lg"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Recruitment Summary</h3>
            <p class="text-sm text-gray-500 mt-1">Export a summary of all recruitment activities.</p>
            <div class="mt-4">
                <a href="{{ route('reports.recruitment-summary') }}" class="block w-full text-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Generate PDF</a>
            </div>
        </div>
    </div>

    <!-- Candidate Filter Modal -->
    <div id="candidateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Candidate Report</h3>
            <form method="GET" action="{{ route('reports.candidates') }}" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Status</option>
                        @foreach(['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'hired', 'rejected'] as $s)
                        <option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('candidateModal').classList.add('hidden')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
</content>

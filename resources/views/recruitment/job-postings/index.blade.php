@extends('layouts.app')

@section('title', 'Job Postings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Job Postings</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your recruitment vacancies.</p>
        </div>
        @can('create_postings')
        <a href="{{ route('recruitment.job-postings.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <i class="fa-solid fa-plus mr-2"></i>New Job Posting
        </a>
        @endcan
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="draft" @selected(request('status') == 'draft')>Draft</option>
            <option value="published" @selected(request('status') == 'published')>Published</option>
            <option value="closed" @selected(request('status') == 'closed')>Closed</option>
        </select>
        <select name="department_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vacancies</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicants</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closing</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($postings as $posting)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $posting->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $posting->department->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $posting->vacancies_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $posting->applications_count ?? $posting->applications->count() }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $posting->status == 'published' ? 'bg-green-100 text-green-800' : ($posting->status == 'closed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">{{ ucfirst($posting->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $posting->closing_date ? \Carbon\Carbon::parse($posting->closing_date)->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                        <a href="{{ route('recruitment.job-postings.show', $posting) }}" class="text-indigo-600 hover:text-indigo-500">View</a>
                        @can('edit_postings')
                        <a href="{{ route('recruitment.job-postings.edit', $posting) }}" class="text-amber-600 hover:text-amber-500">Edit</a>
                        @endcan
                        @can('approve_postings')
                        <form method="POST" action="{{ route('recruitment.job-postings.toggle-status', $posting) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $posting->status == 'published' ? 'red' : 'green' }}-600 hover:text-{{ $posting->status == 'published' ? 'red' : 'green' }}-500">{{ $posting->status == 'published' ? 'Close' : 'Publish' }}</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">No job postings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $postings->links() }}
    </div>
</div>
@endsection

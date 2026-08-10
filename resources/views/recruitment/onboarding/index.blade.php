@extends('layouts.app')

@section('title', 'Onboarding')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Hire Onboarding</h1>
            <p class="mt-1 text-sm text-gray-500">Manage onboarding processes for hired candidates.</p>
        </div>
        @can('manage_onboarding')
        <a href="{{ route('recruitment.onboarding.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"><i class="fa-solid fa-plus mr-1"></i>Start Onboarding</a>
        @endcan
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') == 'pending')>Pending</option>
            <option value="in_progress" @selected(request('status') == 'in_progress')>In Progress</option>
            <option value="completed" @selected(request('status') == 'completed')>Completed</option>
            <option value="on_hold" @selected(request('status') == 'on_hold')>On Hold</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($onboardings as $onboarding)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $onboarding->application->applicant->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $onboarding->application->jobPosting->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($onboarding->start_date)->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 mr-2 max-w-[100px]">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $onboarding->progress }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $onboarding->progress }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $onboarding->status == 'completed' ? 'bg-green-100 text-green-800' : ($onboarding->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : ($onboarding->status == 'on_hold' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800')) }}">{{ str_replace('_', ' ', ucfirst($onboarding->status)) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('recruitment.onboarding.show', $onboarding) }}" class="text-indigo-600 hover:text-indigo-500">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No onboarding processes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $onboardings->links() }}</div>
</div>
@endsection

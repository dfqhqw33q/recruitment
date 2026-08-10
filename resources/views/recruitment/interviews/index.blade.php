@extends('layouts.app')

@section('title', 'Interviews')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Interview Management</h1>
            <p class="mt-1 text-sm text-gray-500">Schedule and manage candidate interviews.</p>
        </div>
        <div class="flex space-x-2">
            @can('view_calendar')
            <a href="{{ route('recruitment.calendar') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"><i class="fa-solid fa-calendar mr-1"></i>Calendar</a>
            @endcan
            @can('schedule_interviews')
            <a href="{{ route('recruitment.interviews.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><i class="fa-solid fa-plus mr-1"></i>Schedule Interview</a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="scheduled" @selected(request('status') == 'scheduled')>Scheduled</option>
            <option value="completed" @selected(request('status') == 'completed')>Completed</option>
            <option value="cancelled" @selected(request('status') == 'cancelled')>Cancelled</option>
            <option value="no_show" @selected(request('status') == 'no_show')>No Show</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interviewer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($interviews as $interview)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $interview->application->applicant->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $interview->application->jobPosting->title }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ $interview->type }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y h:i A') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $interview->interviewer->name }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $interview->status == 'completed' ? 'bg-green-100 text-green-800' : ($interview->status == 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($interview->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('recruitment.interviews.show', $interview) }}" class="text-indigo-600 hover:text-indigo-500">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">No interviews found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $interviews->links() }}
    </div>
</div>
@endsection

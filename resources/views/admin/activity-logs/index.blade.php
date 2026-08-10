@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Activity Logs / Audit Trail</h1>
        <p class="mt-1 text-sm text-gray-500">Review all system activities and audit trail.</p>
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..." class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="module" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Modules</option>
            @foreach(['Recruitment', 'Offers', 'Interviews', 'Onboarding', 'AI Dashboard', 'Documents', 'Departments', 'Job Positions', 'Users'] as $module)
            <option value="{{ $module }}" @selected(request('module') == $module)>{{ $module }}</option>
            @endforeach
        </select>
        <select name="action" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Actions</option>
            @foreach(['create', 'update', 'delete', 'schedule', 'submit', 'status_change', 'ai_generate', 'send', 'respond', 'verify', 'upload', 'login', 'logout'] as $action)
            <option value="{{ $action }}" @selected(request('action') == $action)>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $log->action == 'delete' ? 'bg-red-100 text-red-800' : ($log->action == 'create' ? 'bg-green-100 text-green-800' : ($log->action == 'update' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $log->module }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $log->description }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No activity logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $logs->links() }}</div>
</div>
@endsection
</content>

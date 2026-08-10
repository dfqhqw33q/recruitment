@extends('layouts.app')

@section('title', 'Onboarding Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Onboarding Process</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $onboarding->application->applicant->full_name }} — {{ $onboarding->application->jobPosting->title }}</p>
        </div>
        <div class="flex space-x-2">
            @can('manage_onboarding')
            <form method="POST" action="{{ route('recruitment.onboarding.employee-profile', $onboarding) }}">
                @csrf
                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500"><i class="fa-solid fa-user-plus mr-1"></i>Create Employee Profile</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Progress -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Onboarding Progress</h3>
                <div class="flex items-center mb-4">
                    <div class="flex-1 bg-gray-100 rounded-full h-4 mr-3">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-4 rounded-full transition-all" style="width: {{ $onboarding->progress }}%"></div>
                    </div>
                    <span class="text-xl font-bold text-indigo-600">{{ $onboarding->progress }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="inline-flex text-xs font-semibold px-2.5 py-1 rounded-full {{ $onboarding->status == 'completed' ? 'bg-green-100 text-green-800' : ($onboarding->status == 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">{{ str_replace('_', ' ', ucfirst($onboarding->status)) }}</span>
                    <span class="text-xs text-gray-500">5 Milestones (20% each = 100%)</span>
                </div>
            </div>

            <!-- Checklist -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Onboarding Checklist</h3>
                        <p class="text-xs text-gray-500">Check off completed items (covers 20% of overall progress)</p>
                    </div>
                    @php $completedIds = $onboarding->completed_checklist_ids ?? []; @endphp
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                        {{ is_array($completedIds) ? count($completedIds) : 0 }} / {{ $checklistTemplate->count() }} Done
                    </span>
                </div>
                <form method="POST" action="{{ route('recruitment.onboarding.checklist', $onboarding) }}">
                    @csrf
                    <div class="space-y-3 mb-5">
                        @forelse($checklistTemplate as $item)
                        @php $isDone = in_array($item->id, $completedIds); @endphp
                        <label class="flex items-start p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="checklist_ids[]" value="{{ $item->id }}" 
                                   @checked($isDone)
                                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <p class="text-sm font-medium {{ $isDone ? 'text-gray-400 line-through' : 'text-gray-900' }}">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($item->category) }}</p>
                                @if($item->description)<p class="text-xs text-gray-400 mt-0.5">{{ $item->description }}</p>@endif
                            </div>
                        </label>
                        @empty
                        <p class="text-sm text-gray-500">No checklist items defined.</p>
                        @endforelse
                    </div>
                    @if($checklistTemplate->count() > 0)
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <i class="fa-solid fa-floppy-disk"></i> Save Checklist
                        </button>
                    </div>
                    @endif
                </form>
            </div>

            <!-- Schedule -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Start Date</dt><dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($onboarding->start_date)->format('M d, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Orientation</dt><dd class="font-medium text-gray-900">{{ $onboarding->orientation_date ? \Carbon\Carbon::parse($onboarding->orientation_date)->format('M d, Y') : 'N/A' }}</dd></div>
                    <div><dt class="text-gray-500">Training Start</dt><dd class="font-medium text-gray-900">{{ $onboarding->training_start ? \Carbon\Carbon::parse($onboarding->training_start)->format('M d, Y') : 'N/A' }}</dd></div>
                    <div><dt class="text-gray-500">Training End</dt><dd class="font-medium text-gray-900">{{ $onboarding->training_end ? \Carbon\Carbon::parse($onboarding->training_end)->format('M d, Y') : 'N/A' }}</dd></div>
                </dl>
                @if($onboarding->notes)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $onboarding->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Update Progress</h3>
                <form method="POST" action="{{ route('recruitment.onboarding.update', $onboarding) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Progress (%)</label>
                        <input type="number" name="progress" min="0" max="100" value="{{ $onboarding->progress }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pending" @selected($onboarding->status == 'pending')>Pending</option>
                            <option value="in_progress" @selected($onboarding->status == 'in_progress')>In Progress</option>
                            <option value="completed" @selected($onboarding->status == 'completed')>Completed</option>
                            <option value="on_hold" @selected($onboarding->status == 'on_hold')>On Hold</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ \Carbon\Carbon::parse($onboarding->start_date)->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Orientation Date</label>
                        <input type="date" name="orientation_date" value="{{ $onboarding->orientation_date ? \Carbon\Carbon::parse($onboarding->orientation_date)->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $onboarding->notes }}</textarea>
                    </div>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Assigned Officer</h3>
                <p class="text-sm font-medium text-gray-900">{{ $onboarding->assignedOfficer->name ?? 'N/A' }}</p>
                @if($onboarding->employee)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500">Linked Employee</p>
                    <p class="text-sm font-medium text-gray-900">{{ $onboarding->employee->name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

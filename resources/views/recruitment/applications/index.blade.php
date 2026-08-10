@extends('layouts.app')

@section('title', 'Applicant Pool')

@section('content')
<div style="display:flex; flex-direction:column; align-items:flex-start; margin-bottom:20px;">
    <div style="text-align:left;">
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Applicant Pool</h1>
        <p style="font-size:13px; color:#64748b; margin:0;">Review, filter, and manage all job applications.</p>
    </div>
</div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, or ref code..."
               class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Status</option>
            @foreach($statuses as $key => $label)
            <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="job_posting_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Positions</option>
            @foreach($postings as $posting)
            <option value="{{ $posting->id }}" @selected(request('job_posting_id') == $posting->id)>{{ $posting->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-indigo-600 text-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-indigo-500">Filter</button>
        @if(request()->hasAny(['search', 'status', 'job_posting_id']))
        <a href="{{ route('recruitment.applications.index') }}" class="rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">Clear</a>
        @endif
    </form>

    <!-- Bulk Action Form -->
    <form id="bulk-form" method="POST" action="{{ route('recruitment.applications.bulk-action') }}">
        @csrf

        <!-- Bulk control bar (shown when selections exist) -->
        <div id="bulk-bar"
             style="display:none; align-items:center; gap:0.75rem; background-color:#ffffff; color:#1e293b; border:1px solid #e2e8f0; border-radius:0.5rem; padding:0.75rem 1rem; box-shadow:0 2px 4px rgba(0,0,0,0.05); margin-bottom:1rem;">
            <input type="checkbox" checked id="bulk-bar-checkbox" onclick="clearSelection()" title="Click to unselect all" style="width:1.1rem; height:1.1rem; accent-color:#2563eb; cursor:pointer;">
            <span id="bulk-count" onclick="clearSelection()" title="Click to unselect all" style="background-color:#2563eb; color:#ffffff; font-size:0.875rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:0.375rem; cursor:pointer; display:inline-block;">0 selected</span>
            <div style="flex:1;"></div>

            <button type="button" onclick="bulkSubmit('shortlist')"
                    style="background-color:#059669; color:#ffffff; padding:0.4rem 0.875rem; font-size:0.75rem; font-weight:700; border-radius:0.375rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.375rem;"
                    onmouseover="this.style.backgroundColor='#047857'" onmouseout="this.style.backgroundColor='#059669'">
                <i class="fa-solid fa-star"></i> Shortlist
            </button>
            <button type="button" onclick="bulkSubmit('for_interview')"
                    style="background-color:#d97706; color:#ffffff; padding:0.4rem 0.875rem; font-size:0.75rem; font-weight:700; border-radius:0.375rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.375rem;"
                    onmouseover="this.style.backgroundColor='#b45309'" onmouseout="this.style.backgroundColor='#d97706'">
                <i class="fa-solid fa-video"></i> Move to Interview
            </button>
            <button type="button" onclick="bulkSubmit('assessed')"
                    style="background-color:#2563eb; color:#ffffff; padding:0.4rem 0.875rem; font-size:0.75rem; font-weight:700; border-radius:0.375rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.375rem;"
                    onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                <i class="fa-solid fa-chart-bar"></i> Mark Assessed
            </button>
            <button type="button" onclick="bulkReject()"
                    style="background-color:#dc2626; color:#ffffff; padding:0.4rem 0.875rem; font-size:0.75rem; font-weight:700; border-radius:0.375rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.375rem;"
                    onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                <i class="fa-solid fa-xmark"></i> Reject
            </button>
            <button type="button" onclick="clearSelection()"
                    style="background-color:#475569; color:#ffffff; padding:0.4rem 0.75rem; font-size:0.75rem; font-weight:600; border-radius:0.375rem; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem; margin-left:0.25rem;"
                    onmouseover="this.style.backgroundColor='#334155'" onmouseout="this.style.backgroundColor='#475569'">
                <i class="fa-solid fa-xmark"></i> Clear
            </button>
        </div>

        <!-- Hidden inputs populated by JS -->
        <input type="hidden" name="action" id="bulk-action-input">
        <input type="hidden" name="rejection_reason" id="bulk-rejection-input">

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="check-all" onchange="toggleAll(this)"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Screening / AI</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($applications as $application)
                    <tr class="{{ $application->is_knocked_out ? 'bg-red-50/40' : '' }} transition-colors" id="row-{{ $application->id }}">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $application->id }}"
                                   class="row-check rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   onchange="updateBulkBar()">
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex font-mono text-xs font-bold px-2 py-0.5 rounded bg-gray-100 text-indigo-700 border border-gray-200">
                                {{ $application->reference_code ?? 'APP-'.$application->id }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $application->applicant->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $application->applicant->email }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $application->jobPosting->title }}</td>
                        <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($application->applied_at)->format('M d, Y') }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ in_array($application->status, ['hired'])                              ? 'bg-emerald-100 text-emerald-800' :
                                   (in_array($application->status, ['rejected','withdrawn'])              ? 'bg-red-100 text-red-800' :
                                   (in_array($application->status, ['shortlisted','recommended','assessed']) ? 'bg-green-100 text-green-800' :
                                    'bg-gray-100 text-gray-800')) }}">
                                {{ str_replace('_', ' ', ucfirst($application->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap space-y-1">
                            @if($application->is_knocked_out)
                            <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-300"
                                  title="{{ $application->knockout_reason }}">
                                <i class="fa-solid fa-triangle-exclamation mr-1 text-red-600"></i>Knocked Out
                            </span>
                            @endif
                            @if($application->aiRecommendation)
                            <div>
                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fa-solid fa-brain mr-1 text-indigo-500"></i>{{ $application->aiRecommendation->match_score }}% Match
                                </span>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('recruitment.applications.show', $application) }}"
                               class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">
                                Review <i class="fa-solid fa-chevron-right text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-sm text-gray-500 text-center">No applications found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div>{{ $applications->links() }}</div>
</div>

<!-- Reject reason modal -->
<div id="reject-modal" class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6">
        <h3 class="text-base font-bold text-gray-900 mb-1">Reject Selected Candidates</h3>
        <p class="text-sm text-gray-500 mb-4">Provide a rejection reason — it will be shared with each candidate.</p>
        <textarea id="reject-reason-text" rows="3"
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-400 focus:ring-red-400"
                  placeholder="e.g. Position filled, insufficient experience..."></textarea>
        <div class="flex justify-end gap-3 mt-4">
            <button type="button" onclick="closeRejectModal()"
                    class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</button>
            <button type="button" onclick="confirmReject()"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                <i class="fa-solid fa-xmark mr-1"></i>Confirm Reject
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function getChecked() {
    return [...document.querySelectorAll('.row-check:checked')];
}
function updateBulkBar() {
    const checked = getChecked();
    const bar = document.getElementById('bulk-bar');
    const countEl = document.getElementById('bulk-count');
    const barCb = document.getElementById('bulk-bar-checkbox');
    if (checked.length > 0) {
        bar.style.display = 'flex';
        if (barCb) barCb.checked = true;
    } else {
        bar.style.display = 'none';
        if (barCb) barCb.checked = false;
    }
    countEl.textContent = checked.length + ' selected';

    // Sync select-all checkbox state
    const all = document.querySelectorAll('.row-check');
    document.getElementById('check-all').checked = checked.length === all.length && all.length > 0;
    document.getElementById('check-all').indeterminate = checked.length > 0 && checked.length < all.length;
}
function toggleAll(cb) {
    document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
    updateBulkBar();
}
function clearSelection() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = false);
    document.getElementById('check-all').checked = false;
    updateBulkBar();
}
function bulkSubmit(action) {
    if (getChecked().length === 0) return;
    document.getElementById('bulk-action-input').value = action;
    document.getElementById('bulk-rejection-input').value = '';
    document.getElementById('bulk-form').submit();
}
function bulkReject() {
    if (getChecked().length === 0) return;
    document.getElementById('reject-reason-text').value = '';
    const modal = document.getElementById('reject-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
function confirmReject() {
    const reason = document.getElementById('reject-reason-text').value.trim();
    if (!reason) { alert('Please provide a rejection reason.'); return; }
    document.getElementById('bulk-action-input').value = 'reject';
    document.getElementById('bulk-rejection-input').value = reason;
    closeRejectModal();
    document.getElementById('bulk-form').submit();
}
</script>
@endpush
@endsection

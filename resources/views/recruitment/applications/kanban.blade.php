@extends('layouts.app')

@section('title', 'Recruitment Kanban Board')

@push('styles')
<style>
    /* ── Kanban Layout ─────────────────────────────────────── */
    #kanban-board {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 24px;
        align-items: flex-start;
    }
    .kanban-column {
        min-width: 230px;
        max-width: 230px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .kanban-column-header {
        padding: 10px 12px;
        border-radius: 12px 12px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
    }
    .kanban-cards {
        padding: 10px 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 120px;
    }
    /* ── Card ──────────────────────────────────────────────── */
    .kanban-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 11px;
        cursor: grab;
        transition: box-shadow 0.18s, transform 0.15s;
        position: relative;
        user-select: none;
    }
    .kanban-card:active  { cursor: grabbing; }
    .kanban-card.dragging {
        opacity: 0.45;
        transform: scale(0.97);
        box-shadow: 0 8px 24px rgba(99,102,241,.18);
    }
    .kanban-card:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,0.09);
        border-color: #a5b4fc;
    }
    /* ── Drop Target ──────────────────────────────────────── */
    .kanban-cards.drag-over {
        background: #eef2ff;
        border-radius: 8px;
        outline: 2px dashed #6366f1;
        outline-offset: -2px;
    }
    /* ── Ghost placeholder ─────────────────────────────────── */
    .drag-ghost {
        background: #e0e7ff;
        border: 2px dashed #818cf8;
        border-radius: 8px;
        height: 68px;
        flex-shrink: 0;
    }
    /* ── Column color accents ─────────────────────────────── */
    .col-blue    { background: #eff6ff; border-color: #bfdbfe; }
    .col-indigo  { background: #eef2ff; border-color: #c7d2fe; }
    .col-purple  { background: #faf5ff; border-color: #ddd6fe; }
    .col-emerald { background: #ecfdf5; border-color: #a7f3d0; }
    .col-amber   { background: #fffbeb; border-color: #fde68a; }
    .col-teal    { background: #f0fdfa; border-color: #99f6e4; }
    .col-green   { background: #f0fdf4; border-color: #bbf7d0; }
    /* Match badge colors */
    .match-high  { background:#d1fae5; color:#065f46; }
    .match-mid   { background:#fef3c7; color:#78350f; }
    .match-low   { background:#fee2e2; color:#7f1d1d; }
</style>
@endpush

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recruitment Kanban Board</h1>
            <p class="mt-1 text-sm text-gray-500">Drag and drop candidates across pipeline stages.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('recruitment.applications.index') }}"
               class="inline-flex items-center rounded-md bg-white border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-table-list mr-1.5 text-gray-500"></i>Table View
            </a>
        </div>
    </div>

    <!-- Job Filter -->
    <form method="GET" class="bg-white rounded-lg border border-gray-200 shadow-sm px-4 py-3 flex flex-wrap items-center gap-3">
        <i class="fa-solid fa-filter text-gray-400 text-sm"></i>
        <select name="job_posting_id" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Job Postings</option>
            @foreach($postings as $posting)
            <option value="{{ $posting->id }}" @selected($jobPostingId == $posting->id)>{{ $posting->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-indigo-600 text-white px-4 py-1.5 text-sm font-semibold hover:bg-indigo-500">Apply</button>
        @if($jobPostingId)
        <a href="{{ route('recruitment.kanban.index') }}" class="text-sm text-gray-500 hover:text-gray-800 font-medium">Clear</a>
        @endif

        <!-- Live count badge -->
        <div class="ml-auto flex items-center gap-2">
            @foreach($columns as $col)
            <span title="{{ $col['label'] }}" class="inline-flex items-center text-xs font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                {{ $col['applications']->count() }}
            </span>
            @endforeach
            <span class="text-xs text-gray-500 ml-1">cards total</span>
        </div>
    </form>

    <!-- Kanban Board -->
    <div id="kanban-board">
        @foreach($columns as $col)
        @php
            $colKey = $col['status'];
            $colorMap = [
                'blue'   => 'text-blue-800 bg-blue-50 border-blue-200',
                'indigo' => 'text-indigo-800 bg-indigo-50 border-indigo-200',
                'purple' => 'text-purple-800 bg-purple-50 border-purple-200',
                'emerald'=> 'text-emerald-800 bg-emerald-50 border-emerald-200',
                'amber'  => 'text-amber-800 bg-amber-50 border-amber-200',
                'teal'   => 'text-teal-800 bg-teal-50 border-teal-200',
                'green'  => 'text-green-800 bg-green-50 border-green-200',
            ];
            $dotMap = [
                'blue'   => 'bg-blue-400',  'indigo' => 'bg-indigo-400',
                'purple' => 'bg-purple-400','emerald' => 'bg-emerald-400',
                'amber'  => 'bg-amber-400', 'teal'   => 'bg-teal-400',
                'green'  => 'bg-green-400',
            ];
            $hdrClass = $colorMap[$col['color']] ?? 'text-gray-800 bg-gray-50 border-gray-200';
            $dotClass = $dotMap[$col['color']] ?? 'bg-gray-400';
        @endphp
        <div class="kanban-column" data-status="{{ $colKey }}">
            <!-- Column header -->
            <div class="kanban-column-header {{ $hdrClass }}">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $dotClass }}"></span>
                    <span class="text-xs font-bold uppercase tracking-wide">{{ $col['label'] }}</span>
                </div>
                <span class="text-xs font-black bg-white/70 rounded-full px-2 py-0.5 ml-1 border border-current/20">
                    {{ $col['applications']->count() }}
                </span>
            </div>

            <!-- Droppable card list -->
            <div class="kanban-cards" data-status="{{ $colKey }}">
                @foreach($col['applications'] as $app)
                @php
                    $score = $app->aiRecommendation?->match_score;
                    $matchClass = $score >= 75 ? 'match-high' : ($score >= 50 ? 'match-mid' : 'match-low');
                @endphp
                <div class="kanban-card"
                     draggable="true"
                     data-id="{{ $app->id }}"
                     data-status="{{ $colKey }}"
                     id="card-{{ $app->id }}">

                    <!-- Knocked out indicator -->
                    @if($app->is_knocked_out)
                    <div class="absolute top-2 right-2">
                        <span title="{{ $app->knockout_reason }}" class="inline-flex items-center text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation mr-0.5"></i>KO
                        </span>
                    </div>
                    @endif

                    <!-- Applicant name + ref -->
                    <div class="flex items-start gap-2 pr-6">
                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($app->applicant->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($app->applicant->last_name ?? '', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-gray-900 truncate leading-tight">
                                {{ $app->applicant->full_name }}
                            </p>
                            <p class="text-[10px] text-gray-500 truncate">{{ $app->applicant->email }}</p>
                        </div>
                    </div>

                    <!-- Position -->
                    <p class="text-[10px] text-gray-600 mt-1.5 truncate font-medium">
                        <i class="fa-solid fa-briefcase text-gray-400 mr-0.5"></i>{{ $app->jobPosting->title ?? '—' }}
                    </p>

                    <!-- Footer row: ref code + AI score -->
                    <div class="flex items-center justify-between mt-2 gap-1">
                        <span class="text-[9px] font-mono font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100 truncate">
                            {{ $app->reference_code }}
                        </span>
                        @if($score !== null)
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full shrink-0 {{ $matchClass }}">
                            <i class="fa-solid fa-brain mr-0.5"></i>{{ $score }}%
                        </span>
                        @endif
                    </div>

                    <!-- Applied date -->
                    <p class="text-[9px] text-gray-400 mt-1">Applied {{ \Carbon\Carbon::parse($app->applied_at)->format('M d') }}</p>

                    <!-- Quick-view link -->
                    <a href="{{ route('recruitment.applications.show', $app) }}"
                       class="absolute bottom-2 right-2 text-gray-300 hover:text-indigo-600 transition-colors"
                       title="View application" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Move feedback toast -->
    <div id="kanban-toast"
         class="fixed bottom-6 right-6 z-50 hidden items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2.5 rounded-lg shadow-lg">
        <i class="fa-solid fa-check-circle text-emerald-400"></i>
        <span id="kanban-toast-msg"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const MOVE_URL_TEMPLATE = "{{ route('recruitment.kanban.move', ['application' => '__ID__']) }}";
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let dragged = null;
    let ghost   = null;

    /* ── Drag start ─────────────────────────────────────────── */
    document.querySelectorAll('.kanban-card').forEach(card => {
        card.addEventListener('dragstart', e => {
            dragged = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.id);
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            document.querySelectorAll('.drag-ghost').forEach(g => g.remove());
            document.querySelectorAll('.kanban-cards').forEach(c => c.classList.remove('drag-over'));
            dragged = null;
            ghost   = null;
        });
    });

    /* ── Drop zones ─────────────────────────────────────────── */
    document.querySelectorAll('.kanban-cards').forEach(zone => {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zone.classList.add('drag-over');

            // Ghost placeholder
            if (!ghost || ghost.parentNode !== zone) {
                document.querySelectorAll('.drag-ghost').forEach(g => g.remove());
                ghost = document.createElement('div');
                ghost.className = 'drag-ghost';
                zone.appendChild(ghost);
            }
        });

        zone.addEventListener('dragleave', e => {
            if (!zone.contains(e.relatedTarget)) {
                zone.classList.remove('drag-over');
                if (ghost && ghost.parentNode === zone) ghost.remove();
            }
        });

        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            if (ghost) { ghost.remove(); ghost = null; }

            if (!dragged) return;

            const newStatus = zone.dataset.status;
            const oldStatus = dragged.dataset.status;
            const appId     = dragged.dataset.id;

            // Move the card DOM
            zone.appendChild(dragged);
            dragged.dataset.status = newStatus;

            // Update column counts
            updateCounts();

            if (oldStatus === newStatus) return;

            // Persist via AJAX
            const url = MOVE_URL_TEMPLATE.replace('__ID__', appId);
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    toast(`Moved to "${labelFor(newStatus)}" and applicant notified.`);
                } else {
                    toast('Move failed — please refresh.', true);
                    dragged.dataset.status = oldStatus;
                }
            })
            .catch(() => toast('Network error — please refresh.', true));
        });
    });

    /* ── Helpers ──────────────────────────────────────────── */
    function updateCounts() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            const status = col.dataset.status;
            const count  = col.querySelectorAll('.kanban-card').length;
            const badge  = col.querySelector('.kanban-column-header span:last-child');
            if (badge) badge.textContent = count;
        });
    }

    function labelFor(status) {
        const map = {
            submitted:'Submitted', under_review:'Under Review', screening:'Screening',
            shortlisted:'Shortlisted', for_interview:'Interview', assessed:'Assessed',
            recommended:'Recommended', hired:'Hired'
        };
        return map[status] ?? status;
    }

    function toast(msg, isError = false) {
        const el  = document.getElementById('kanban-toast');
        const txt = document.getElementById('kanban-toast-msg');
        const ico = el.querySelector('i');
        txt.textContent = msg;
        ico.className = isError ? 'fa-solid fa-circle-xmark text-red-400' : 'fa-solid fa-check-circle text-emerald-400';
        el.classList.remove('hidden');
        el.classList.add('flex');
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.classList.add('hidden'); el.classList.remove('flex'); }, 3500);
    }
})();
</script>
@endpush

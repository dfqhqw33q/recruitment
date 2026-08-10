@extends('layouts.employee')
@section('title', 'My Onboarding')
@section('page-title', 'My Onboarding')
@push('styles')
<style>
.onboarding-hero{background:linear-gradient(135deg,#0f172a,#4F39F6);border-radius:14px;padding:24px 28px;color:#fff;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.progress-track{background:rgba(255,255,255,.15);border-radius:99px;height:10px;overflow:hidden;margin:10px 0}
.progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#10b981,#f59e0b);transition:width .6s ease}
.progress-label{font-size:11px;color:#94a3b8;font-weight:500}
.hero-progress-pct{font-size:36px;font-weight:800;color:#10b981}
.timeline{position:relative;padding-left:28px}
.timeline::before{content:'';position:absolute;left:8px;top:4px;bottom:4px;width:2px;background:#e2e8f0;border-radius:1px}
.tl-item{position:relative;margin-bottom:24px}
.tl-dot{position:absolute;left:-24px;width:16px;height:16px;border-radius:50%;top:2px;flex-shrink:0}
.tl-dot-green{background:#10b981;box-shadow:0 0 0 3px #d1fae5}
.tl-dot-blue{background:#3b82f6;box-shadow:0 0 0 3px #dbeafe}
.tl-dot-gray{background:#cbd5e1;box-shadow:0 0 0 3px #f1f5f9}
.tl-title{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:3px}
.tl-date{font-size:12px;color:#64748b;margin-bottom:6px}
.tl-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:#d1fae5;color:#065f46}
.badge-blue{background:#dbeafe;color:#1e40af}
.badge-gray{background:#f1f5f9;color:#475569}
.badge-amber{background:#fef3c7;color:#92400e}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
.info-row:last-child{border-bottom:none}
</style>
@endpush
@section('content')

@if($onboarding)
{{-- Hero --}}
<div class="onboarding-hero">
    <div style="flex:1">
        <p style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6ee7b7;margin-bottom:6px">Your Onboarding Journey</p>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:12px">{{ $profile ? $profile->first_name . "'s" : 'Your' }} Onboarding Plan</h2>
        <div class="progress-track">
            <div class="progress-fill" style="width:{{ $onboarding->progress ?? 0 }}%"></div>
        </div>
        <p class="progress-label">{{ $onboarding->progress ?? 0 }}% complete</p>
    </div>
    <div style="text-align:center;flex-shrink:0">
        <div class="hero-progress-pct">{{ $onboarding->progress ?? 0 }}%</div>
        <p style="font-size:12px;color:#94a3b8;margin-top:4px">Overall<br>Progress</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px">
    {{-- Timeline --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-timeline" style="color:#10b981;margin-right:6px"></i>Onboarding Timeline</div>
            @php $statusClass = match($onboarding->status ?? 'pending'){ 'completed'=>'badge-green','in_progress'=>'badge-blue',default=>'badge-gray' }; @endphp
            <span class="tl-badge {{ $statusClass }}">{{ ucfirst(str_replace('_',' ',$onboarding->status ?? 'Pending')) }}</span>
        </div>
        <div class="card-body">
            <div class="timeline">
                @if($onboarding->start_date)
                <div class="tl-item">
                    <div class="tl-dot {{ now()->gte($onboarding->start_date) ? 'tl-dot-green' : 'tl-dot-gray' }}"></div>
                    <div class="tl-title">Employment Start Date</div>
                    <div class="tl-date">{{ $onboarding->start_date->format('l, F d, Y') }}</div>
                    <span class="tl-badge {{ now()->gte($onboarding->start_date) ? 'badge-green' : 'badge-gray' }}">
                        {{ now()->gte($onboarding->start_date) ? 'Completed' : 'Upcoming' }}
                    </span>
                </div>
                @endif

                @if($onboarding->orientation_date)
                <div class="tl-item">
                    <div class="tl-dot {{ now()->gte($onboarding->orientation_date) ? 'tl-dot-green' : 'tl-dot-gray' }}"></div>
                    <div class="tl-title">Company Orientation</div>
                    <div class="tl-date">{{ $onboarding->orientation_date->format('l, F d, Y') }}</div>
                    <span class="tl-badge {{ now()->gte($onboarding->orientation_date) ? 'badge-green' : 'badge-gray' }}">
                        {{ now()->gte($onboarding->orientation_date) ? 'Done' : 'Scheduled' }}
                    </span>
                </div>
                @endif

                @if($onboarding->training_start)
                <div class="tl-item">
                    @php $trainingDone = $onboarding->training_end && now()->gt($onboarding->training_end); @endphp
                    <div class="tl-dot {{ $trainingDone ? 'tl-dot-green' : (now()->gte($onboarding->training_start) ? 'tl-dot-blue' : 'tl-dot-gray') }}"></div>
                    <div class="tl-title">Training Period</div>
                    <div class="tl-date">{{ $onboarding->training_start->format('M d') }} – {{ $onboarding->training_end ? $onboarding->training_end->format('M d, Y') : 'TBD' }}</div>
                    <span class="tl-badge {{ $trainingDone ? 'badge-green' : (now()->gte($onboarding->training_start) ? 'badge-blue' : 'badge-gray') }}">
                        {{ $trainingDone ? 'Completed' : (now()->gte($onboarding->training_start) ? 'In Progress' : 'Upcoming') }}
                    </span>
                </div>
                @endif

                <div class="tl-item">
                    <div class="tl-dot {{ ($onboarding->status ?? '') === 'completed' ? 'tl-dot-green' : 'tl-dot-gray' }}"></div>
                    <div class="tl-title">Onboarding Completion</div>
                    @if($onboarding->completed_at)
                        <div class="tl-date">{{ $onboarding->completed_at->format('l, F d, Y') }}</div>
                        <span class="tl-badge badge-green">Completed</span>
                    @else
                        <div class="tl-date">Pending completion</div>
                        <span class="tl-badge badge-gray">Not yet complete</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Info panel --}}
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-header"><div class="card-title"><i class="fa-solid fa-circle-info" style="color:#10b981;margin-right:6px"></i>Details</div></div>
            <div class="card-body" style="padding:12px 20px">
                @if($onboarding->assignedOfficer)
                <div class="info-row">
                    <span style="color:#64748b;font-weight:500">HR Officer</span>
                    <span style="font-weight:600;color:#0f172a">{{ $onboarding->assignedOfficer->name }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span style="color:#64748b;font-weight:500">Status</span>
                    <span class="tl-badge {{ $statusClass }}">{{ ucfirst(str_replace('_',' ',$onboarding->status ?? 'Pending')) }}</span>
                </div>
                <div class="info-row">
                    <span style="color:#64748b;font-weight:500">Progress</span>
                    <span style="font-weight:700;color:#10b981">{{ $onboarding->progress ?? 0 }}%</span>
                </div>
            </div>
        </div>

        @if($onboarding->notes)
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fa-solid fa-note-sticky" style="color:#f59e0b;margin-right:6px"></i>HR Notes</div></div>
            <div class="card-body">
                <p style="font-size:13px;color:#334155;line-height:1.6">{{ $onboarding->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

@else
<div class="card">
    <div class="card-body" style="text-align:center;padding:56px 24px">
        <i class="fa-solid fa-clipboard-list" style="font-size:52px;color:#cbd5e1;display:block;margin-bottom:16px"></i>
        <h3 style="font-size:17px;font-weight:700;color:#334155;margin-bottom:8px">No Onboarding Plan Yet</h3>
        <p style="font-size:13.5px;color:#94a3b8;max-width:380px;margin:0 auto">Your HR officer will assign your onboarding schedule soon. Check back here to track your onboarding progress.</p>
    </div>
</div>
@endif
@endsection

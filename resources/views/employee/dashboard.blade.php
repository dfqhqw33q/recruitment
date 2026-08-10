@extends('layouts.employee')

@section('title', 'Dashboard')
@section('page-title', 'Employee Dashboard')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px; text-align: left; width: 100%;">
    {{-- Welcome Header Card --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left; width: 100%;">
        <div style="display: flex; align-items: center; justify-content: space-between; text-align: left; flex-wrap: wrap; gap: 16px; width: 100%;">
            <div style="text-align: left;">
                <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; text-align: left;">Welcome, {{ auth()->user()->name }} 👋</h2>
                <p style="font-size: 14px; color: #64748b; margin-top: 4px; margin-bottom: 0; text-align: left;">Here is your real-time onboarding overview and employee portal status.</p>
            </div>
            <div style="text-align: left;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background: #d1fae5; color: #065f46; font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 9999px;">
                    <i class="fa-solid fa-circle-check" style="font-size: 10px;"></i> Active Employee
                </span>
            </div>
        </div>
    </div>

    {{-- Quick Stats Grid (3 columns) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; width: 100%;">
        {{-- Card 1: Job Applied --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left;">
            <div style="display: flex; align-items: center; gap: 16px; text-align: left;">
                <div style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div style="min-width: 0; flex: 1; text-align: left;">
                    <p style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; text-align: left;">Job Applied</p>
                    <p style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 2px; margin-bottom: 0; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; text-align: left;" title="{{ $appliedJobTitle }}">{{ $appliedJobTitle }}</p>
                    <p style="font-size: 12px; color: #94a3b8; margin-top: 2px; margin-bottom: 0; text-align: left;">{{ $departmentName }}</p>
                </div>
            </div>
        </div>

        {{-- Card 2: Real-Time Onboarding Status --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left;">
            <div style="display: flex; align-items: center; gap: 16px; text-align: left;">
                <div style="width: 48px; height: 48px; background: #d1fae5; color: #059669; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="fa-solid fa-rocket"></i>
                </div>
                <div style="min-width: 0; flex: 1; text-align: left;">
                    <p style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; text-align: left;">Onboarding Status</p>
                    <div style="margin-top: 4px; text-align: left;">
                        @if($onboarding)
                            @if($onboarding->status === 'completed')
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #d1fae5; color: #065f46; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                    <i class="fa-solid fa-circle-check"></i> Completed (100%)
                                </span>
                            @elseif($onboarding->status === 'in_progress')
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #e0e7ff; color: #4338ca; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                    <i class="fa-solid fa-spinner fa-spin"></i> In Progress ({{ $onboarding->progress ?? 0 }}%)
                                </span>
                            @elseif($onboarding->status === 'on_hold')
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                    <i class="fa-solid fa-circle-pause"></i> On Hold
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #f1f5f9; color: #475569; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                    <i class="fa-solid fa-clock"></i> Pending
                                </span>
                            @endif
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 4px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                <i class="fa-solid fa-clock"></i> Pending HR Setup
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Unread Notifications --}}
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: left;">
            <div style="display: flex; align-items: center; gap: 16px; text-align: left;">
                <div style="width: 48px; height: 48px; background: #fef3c7; color: #d97706; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div style="min-width: 0; flex: 1; text-align: left;">
                    <p style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; text-align: left;">Unread Notifications</p>
                    <p style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 2px; margin-bottom: 0; text-align: left;">{{ $unreadCount }}</p>
                    <a href="{{ route('employee.notifications') }}" style="font-size: 12px; font-weight: 600; color: #4f46e5; text-decoration: none; display: inline-block; margin-top: 2px;">View all &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Section: Real-Time Onboarding Overview & Progress --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; text-align: left;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 16px 24px; text-align: left;">
            <div style="display: flex; align-items: center; gap: 8px; text-align: left;">
                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">Onboarding & Orientation Progress</h3>
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;" title="Real-time status"></span>
            </div>
            <a href="{{ route('employee.onboarding') }}" style="font-size: 12px; font-weight: 600; color: #4f46e5; text-decoration: none;">
                Full Details <i class="fa-solid fa-arrow-right" style="margin-left: 4px;"></i>
            </a>
        </div>
        <div style="padding: 24px; text-align: left;">
            @if($onboarding)
                <div style="display: flex; flex-direction: column; gap: 20px; text-align: left;">
                    {{-- Progress Bar --}}
                    <div style="text-align: left;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 12px; font-weight: 600; color: #334155;">Overall Completion</span>
                            <span style="font-size: 12px; font-weight: 700; color: #4f46e5;">{{ $onboarding->progress ?? 0 }}%</span>
                        </div>
                        <div style="width: 100%; background: #f1f5f9; border-radius: 9999px; height: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                            <div style="background: #4f46e5; height: 12px; border-radius: 9999px; width: {{ min(100, max(0, $onboarding->progress ?? 0)) }}%; transition: width 0.5s ease;"></div>
                        </div>
                    </div>

                    {{-- Schedule Grid --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; padding-top: 8px;">
                        <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 16px; text-align: left;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-calendar-day" style="color: #6366f1;"></i> Start Date
                            </div>
                            <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">
                                {{ $onboarding->start_date ? $onboarding->start_date->format('M d, Y') : 'To be scheduled' }}
                            </p>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 16px; text-align: left;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-users" style="color: #6366f1;"></i> Orientation Date
                            </div>
                            <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">
                                {{ $onboarding->orientation_date ? $onboarding->orientation_date->format('M d, Y') : 'To be scheduled' }}
                            </p>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 16px; text-align: left;">
                            <div style="font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-graduation-cap" style="color: #6366f1;"></i> Training Period
                            </div>
                            <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">
                                @if($onboarding->training_start)
                                    {{ $onboarding->training_start->format('M d') }} - {{ $onboarding->training_end ? $onboarding->training_end->format('M d, Y') : 'Ongoing' }}
                                @else
                                    Not scheduled yet
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($onboarding->notes)
                        <div style="background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px; padding: 14px; font-size: 12px; color: #1e40af; text-align: left;">
                            <p style="font-weight: 700; margin: 0 0 4px 0;"><i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> HR Instructions & Notes:</p>
                            <p style="margin: 0; color: #1e3a8a;">{{ $onboarding->notes }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div style="text-align: left; padding: 8px 0;">
                    <div style="display: flex; align-items: flex-start; gap: 14px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 18px; text-align: left;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div style="text-align: left;">
                            <h4 style="font-size: 14px; font-weight: 700; color: #92400e; margin: 0; text-align: left;">Onboarding Schedule Pending</h4>
                            <p style="font-size: 13px; color: #b45309; margin-top: 4px; margin-bottom: 0; text-align: left;">
                                Your application for <strong style="color: #78350f;">{{ $appliedJobTitle }}</strong> was approved and your employee account is active. HR is currently preparing your orientation and onboarding checklist.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Notifications Card --}}
    @if($notifications->count() > 0)
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; text-align: left;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 16px 24px; text-align: left;">
            <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0;">Recent Notifications</h3>
            <a href="{{ route('employee.notifications') }}" style="font-size: 12px; font-weight: 600; color: #4f46e5; text-decoration: none;">View All</a>
        </div>
        <div style="display: flex; flex-direction: column; text-align: left;">
            @foreach($notifications as $notif)
            <div style="padding: 16px 24px; display: flex; align-items: flex-start; gap: 12px; border-bottom: 1px solid #f1f5f9; text-align: left;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $notif->is_read ? '#f1f5f9' : '#e0e7ff' }}; color: {{ $notif->is_read ? '#94a3b8' : '#4f46e5' }}; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0;">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div style="min-width: 0; flex: 1; text-align: left;">
                    <p style="font-size: 13px; font-weight: 600; color: #0f172a; margin: 0; text-align: left;">{{ $notif->title }}</p>
                    <p style="font-size: 12px; color: #475569; margin-top: 2px; margin-bottom: 0; text-align: left;">{{ $notif->message }}</p>
                    <span style="font-size: 10px; color: #94a3b8; margin-top: 4px; display: block; text-align: left;">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

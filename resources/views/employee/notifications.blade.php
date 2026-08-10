@extends('layouts.employee')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('content')
<div style="max-width:700px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <h2 style="font-size:20px;font-weight:800;color:#0f172a">Notifications</h2>
            <p style="font-size:13px;color:#64748b;margin-top:2px">All your recent updates and alerts</p>
        </div>
        @if($notifications->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('employee.notifications.read-all') }}">
            @csrf
            <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;border:1px solid #c7d2fe;background:#eef2ff;color:#4338ca;font-size:13px;font-weight:600;cursor:pointer;transition:background 0.15s;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                Mark All as Read
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;color:#15803d;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;flex-shrink:0;color:#16a34a" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
        {{ session('success') }}
    </div>
    @endif
    <div class="card">
        <div class="card-body" style="padding:0">
            @forelse($notifications as $n)
            <div style="display:flex;gap:14px;padding:16px 20px;border-bottom:1px solid #f1f5f9;{{ !$n->is_read ? 'background:#f0fdf4' : '' }}">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ !$n->is_read ? '#10b981' : '#cbd5e1' }};flex-shrink:0;margin-top:5px"></div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:13.5px;color:#1e293b;line-height:1.5;font-weight:{{ !$n->is_read ? '600' : '400' }}">{{ $n->message }}</p>
                    <p style="font-size:11.5px;color:#94a3b8;margin-top:4px">{{ $n->created_at->diffForHumans() }} &bull; {{ $n->created_at->format('M d, Y h:i A') }}</p>
                </div>
                @if($n->type ?? false)
                <span style="font-size:10.5px;font-weight:600;padding:3px 8px;border-radius:99px;background:#f1f5f9;color:#64748b;white-space:nowrap;align-self:flex-start">{{ str_replace('_',' ',ucfirst($n->type)) }}</span>
                @endif
            </div>
            @empty
            <div style="text-align:center;padding:56px 24px">
                <i class="fa-solid fa-bell-slash" style="font-size:48px;color:#cbd5e1;display:block;margin-bottom:14px"></i>
                <h3 style="font-size:16px;font-weight:700;color:#334155;margin-bottom:6px">No notifications yet</h3>
                <p style="font-size:13.5px;color:#94a3b8">You are all caught up! New notifications will appear here.</p>
            </div>
            @endforelse
        </div>
    </div>
    <div style="margin-top:16px">{{ $notifications->links() }}</div>
</div>
@endsection

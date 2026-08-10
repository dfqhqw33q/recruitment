@extends('layouts.employee')
@section('title', 'My Documents')
@section('page-title', 'My Documents')
@push('styles')
<style>
.doc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.doc-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.06);display:flex;flex-direction:column;gap:12px;transition:box-shadow .2s}
.doc-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1)}
.doc-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.doc-icon-pdf{background:#fee2e2;color:#dc2626}
.doc-icon-img{background:#dbeafe;color:#2563eb}
.doc-icon-doc{background:#ede9fe;color:#7c3aed}
.doc-icon-gen{background:#f1f5f9;color:#64748b}
.doc-name{font-size:13.5px;font-weight:700;color:#0f172a;line-height:1.3}
.doc-type{font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em}
.doc-meta{font-size:11px;color:#94a3b8}
.doc-status{display:inline-flex;align-items:center;padding:3px 8px;border-radius:99px;font-size:10.5px;font-weight:700}
.status-verified{background:#d1fae5;color:#065f46}
.status-pending{background:#fef3c7;color:#92400e}
.status-rejected{background:#fee2e2;color:#991b1b}
.doc-actions{display:flex;gap:8px;margin-top:auto}
.btn-sm{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:none;transition:background .15s}
.btn-outline{background:#f1f5f9;color:#334155;border:1px solid #e2e8f0}
.btn-outline:hover{background:#e2e8f0}
</style>
@endpush
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h2 style="font-size:20px;font-weight:800;color:#0f172a">My Documents</h2>
        <p style="font-size:13px;color:#64748b;margin-top:2px">View and download your employment documents</p>
    </div>
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:8px 14px;font-size:12.5px;font-weight:600;color:#065f46">
        <i class="fa-solid fa-folder-open" style="margin-right:5px"></i> {{ $documents->count() }} document(s)
    </div>
</div>

@if($documents->count())
<div class="doc-grid">
    @foreach($documents as $doc)
    @php
        $ext = strtolower(pathinfo($doc->file_path ?? '', PATHINFO_EXTENSION));
        $iconClass = match(true){ in_array($ext,['pdf'])=>'doc-icon-pdf', in_array($ext,['jpg','jpeg','png','gif'])=>'doc-icon-img', in_array($ext,['doc','docx'])=>'doc-icon-doc', default=>'doc-icon-gen' };
        $iconName = match(true){ in_array($ext,['pdf'])=>'fa-file-pdf', in_array($ext,['jpg','jpeg','png','gif'])=>'fa-file-image', in_array($ext,['doc','docx'])=>'fa-file-word', default=>'fa-file' };
        $statusClass = match($doc->status ?? 'pending'){ 'verified'=>'status-verified','rejected'=>'status-rejected', default=>'status-pending' };
    @endphp
    <div class="doc-card">
        <div style="display:flex;align-items:flex-start;gap:12px">
            <div class="doc-icon {{ $iconClass }}"><i class="fa-solid {{ $iconName }}"></i></div>
            <div style="min-width:0;flex:1">
                <div class="doc-name">{{ $doc->document_name }}</div>
                <div class="doc-type">{{ str_replace('_',' ',$doc->document_type ?? 'Document') }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between">
            <span class="doc-status {{ $statusClass }}">{{ ucfirst($doc->status ?? 'Pending') }}</span>
            <span class="doc-meta">{{ $doc->created_at->format('M d, Y') }}</span>
        </div>
        @if($doc->file_size)
        <div class="doc-meta">{{ number_format($doc->file_size / 1024, 1) }} KB</div>
        @endif
        <div class="doc-actions">
            @if($doc->file_path && file_exists(storage_path('app/public/' . $doc->file_path)))
            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn-sm btn-outline">
                <i class="fa-solid fa-eye"></i> View
            </a>
            <a href="{{ asset('storage/' . $doc->file_path) }}" download class="btn-sm btn-outline">
                <i class="fa-solid fa-download"></i> Download
            </a>
            @else
            <span class="btn-sm btn-outline" style="opacity:.5;cursor:default"><i class="fa-solid fa-file-circle-xmark"></i> File unavailable</span>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body" style="text-align:center;padding:56px 24px">
        <i class="fa-solid fa-folder-open" style="font-size:52px;color:#cbd5e1;display:block;margin-bottom:16px"></i>
        <h3 style="font-size:17px;font-weight:700;color:#334155;margin-bottom:8px">No Documents Yet</h3>
        <p style="font-size:13.5px;color:#94a3b8;max-width:380px;margin:0 auto">Your employment documents (offer letter, contract, etc.) will appear here once uploaded by HR.</p>
    </div>
</div>
@endif
@endsection

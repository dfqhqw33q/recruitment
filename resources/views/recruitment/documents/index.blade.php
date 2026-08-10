@extends('layouts.app')

@section('title', 'Document Verification')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Document Verification</h1>
            <p class="mt-1 text-sm text-gray-500">Review and verify applicant documents.</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') == 'pending')>Pending</option>
            <option value="verified" @selected(request('status') == 'verified')>Verified</option>
            <option value="rejected" @selected(request('status') == 'rejected')>Rejected</option>
        </select>
        <select name="document_type" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Types</option>
            <option value="resume" @selected(request('document_type') == 'resume')>Resume</option>
            <option value="diploma" @selected(request('document_type') == 'diploma')>Diploma</option>
            <option value="transcript" @selected(request('document_type') == 'transcript')>Transcript</option>
            <option value="certificate" @selected(request('document_type') == 'certificate')>Certificate</option>
            <option value="government_id" @selected(request('document_type') == 'government_id')>Government ID</option>
            <option value="contract" @selected(request('document_type') == 'contract')>Contract</option>
            <option value="other" @selected(request('document_type') == 'other')>Other</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $document)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $document->document_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $document->applicant->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $document->document_type) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($document->created_at)->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $document->status == 'verified' ? 'bg-green-100 text-green-800' : ($document->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($document->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                        <a href="{{ route('recruitment.documents.download', $document) }}" class="text-indigo-600 hover:text-indigo-500"><i class="fa-solid fa-download"></i></a>
                        @can('verify_documents')
                        <form method="POST" action="{{ route('recruitment.documents.verify', $document) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="verified">
                            <button type="submit" class="text-green-600 hover:text-green-500" title="Verify Document"><i class="fa-solid fa-check"></i></button>
                        </form>
                        <form method="POST" action="{{ route('recruitment.documents.verify', $document) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-red-600 hover:text-red-500" title="Reject Document"><i class="fa-solid fa-xmark"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No documents found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $documents->links() }}</div>
</div>
@endsection

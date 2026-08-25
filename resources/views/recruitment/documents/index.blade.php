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
                        <!-- Preview Button -->
                        <button type="button"
                                onclick="openDocModal('{{ route('recruitment.documents.preview', $document) }}', '{{ addslashes($document->document_name) }}', '{{ route('recruitment.documents.download', $document) }}', '{{ strtolower($document->file_type) }}', '{{ $document->applicant->full_name }}', '{{ route('recruitment.documents.verify', $document) }}')"
                                class="text-indigo-600 hover:text-indigo-900 inline-flex items-center p-1 rounded hover:bg-indigo-50"
                                title="Preview Document">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <a href="{{ route('recruitment.documents.download', $document) }}" class="text-gray-600 hover:text-gray-900 inline-flex items-center p-1 rounded hover:bg-gray-100" title="Download File"><i class="fa-solid fa-download"></i></a>
                        @can('verify_documents')
                        <form method="POST" action="{{ route('recruitment.documents.verify', $document) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="verified">
                            <button type="submit" class="text-green-600 hover:text-green-800 inline-flex items-center p-1 rounded hover:bg-green-50" title="Verify Document"><i class="fa-solid fa-check"></i></button>
                        </form>
                        <form method="POST" action="{{ route('recruitment.documents.verify', $document) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-red-600 hover:text-red-800 inline-flex items-center p-1 rounded hover:bg-red-50" title="Reject Document"><i class="fa-solid fa-xmark"></i></button>
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

<!-- DOCUMENT PREVIEW MODAL -->
<div id="docPreviewModal" class="hidden fixed inset-0 z-50 overflow-hidden bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full h-[95vh] max-w-5xl flex flex-col overflow-hidden border border-gray-200">
        <!-- Modal Top Bar -->
        <div class="px-6 py-3.5 bg-gray-900 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white" id="docModalTitle">Document Preview</h3>
                    <p class="text-xs text-gray-400 mt-0.5" id="docModalSubtitle">Applicant Document</p>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center gap-2">
                <a id="docModalDownloadBtn" href="#"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-md shadow-sm transition-colors">
                    <i class="fa-solid fa-download"></i>
                    <span>Download</span>
                </a>
                <a id="docModalNewTabBtn" href="#" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-md transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>New Tab</span>
                </a>
                <button type="button"
                        onclick="closeDocModal()"
                        class="w-8 h-8 rounded-md bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 flex items-center justify-center transition-colors"
                        title="Close Modal (Esc)">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Modal Viewer Body -->
        <div class="flex-1 bg-gray-100 relative overflow-hidden" id="docModalBody">
            <iframe id="docModalIframe" src="" style="width:100%; height:100%; min-height:75vh; border:none; display:block;" title="Document Viewer"></iframe>
            <div id="docModalImageContainer" class="hidden w-full h-full flex items-center justify-center p-4 overflow-auto">
                <img id="docModalImg" src="" alt="Document" class="max-w-full max-h-full object-contain rounded shadow">
            </div>
        </div>

        <!-- Modal Footer Actions -->
        @can('verify_documents')
        <div class="px-6 py-3 bg-white border-t border-gray-200 flex items-center justify-between shrink-0">
            <span class="text-xs text-gray-500 font-medium">Verify or Reject this document:</span>
            <div class="flex items-center gap-2">
                <form id="docModalVerifyForm" method="POST" action="" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="verified">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-green-600 text-white text-xs font-semibold hover:bg-green-500 shadow-sm">
                        <i class="fa-solid fa-check"></i> Verify Document
                    </button>
                </form>
                <form id="docModalRejectForm" method="POST" action="" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-red-600 text-white text-xs font-semibold hover:bg-red-500 shadow-sm">
                        <i class="fa-solid fa-xmark"></i> Reject Document
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>

@push('scripts')
<script>
function openDocModal(previewUrl, docName, downloadUrl, fileType, applicantName, verifyUrl) {
    const modal = document.getElementById('docPreviewModal');
    const title = document.getElementById('docModalTitle');
    const subtitle = document.getElementById('docModalSubtitle');
    const downloadBtn = document.getElementById('docModalDownloadBtn');
    const newTabBtn = document.getElementById('docModalNewTabBtn');
    const iframe = document.getElementById('docModalIframe');
    const imgContainer = document.getElementById('docModalImageContainer');
    const img = document.getElementById('docModalImg');
    const verifyForm = document.getElementById('docModalVerifyForm');
    const rejectForm = document.getElementById('docModalRejectForm');

    title.textContent = docName;
    subtitle.textContent = applicantName ? `Applicant: ${applicantName}` : 'Document Preview';
    downloadBtn.href = downloadUrl;
    newTabBtn.href = previewUrl;

    if (verifyForm) verifyForm.action = verifyUrl;
    if (rejectForm) rejectForm.action = verifyUrl;

    const imgTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (imgTypes.includes(fileType)) {
        iframe.classList.add('hidden');
        iframe.src = '';
        imgContainer.classList.remove('hidden');
        img.src = previewUrl;
    } else {
        imgContainer.classList.add('hidden');
        img.src = '';
        iframe.classList.remove('hidden');
        iframe.src = previewUrl + '#toolbar=1&navpanes=0&view=FitH';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDocModal() {
    const modal = document.getElementById('docPreviewModal');
    const iframe = document.getElementById('docModalIframe');
    const img = document.getElementById('docModalImg');

    if (iframe) iframe.src = '';
    if (img) img.src = '';

    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDocModal();
    }
});
</script>
@endpush
@endsection

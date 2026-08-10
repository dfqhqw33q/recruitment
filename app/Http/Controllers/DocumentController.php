<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\UploadedDocument;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = UploadedDocument::with('applicant', 'application', 'verifier')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->document_type, fn($q, $t) => $q->where('document_type', $t))
            ->latest();

        $documents = $query->paginate(15);
        return view('recruitment.documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'application_id' => 'nullable|exists:applications,id',
            'document_type' => 'required|in:resume,diploma,transcript,certificate,government_id,contract,other',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $data['applicant_id'], 'public');

        $data['file_path'] = $path;
        $data['file_type'] = $file->getClientOriginalExtension();
        $data['file_size'] = $file->getSize();
        $data['uploaded_by'] = auth()->id();
        $data['status'] = 'pending';

        $document = UploadedDocument::create($data);

        app(ActivityLogService::class)->log(
            'upload', 'Documents',
            "Document '{$document->document_name}' uploaded.",
            'UploadedDocument', $document->id
        );

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function verify(Request $request, UploadedDocument $document)
    {
        $data = $request->validate([
            'status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string',
        ]);

        $document->update([
            'status' => $data['status'],
            'verification_notes' => $data['verification_notes'] ?? null,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        app(ActivityLogService::class)->log(
            'verify', 'Documents',
            "Document '{$document->document_name}' marked as {$data['status']}.",
            'UploadedDocument', $document->id
        );

        return back()->with('success', 'Document status updated.');
    }

    public function download(UploadedDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File not found.');
        }
        return Storage::disk('public')->download($document->file_path, $document->document_name);
    }

    public function destroy(UploadedDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }
}

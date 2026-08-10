<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\ApplicantExperience;
use App\Models\ApplicantSkill;
use App\Models\Application;
use App\Models\Certification;
use App\Models\JobPosting;
use App\Models\NotificationRecord;
use App\Models\OfferLetter;
use App\Models\UploadedDocument;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicantPortalController extends Controller
{
    public function profile()
    {
        $applicant = auth()->user()->applicant;
        if (!$applicant) {
            $applicant = new Applicant(['user_id' => auth()->id()]);
        } else {
            $applicant->load(['education', 'experiences', 'skills', 'certifications', 'documents']);
        }
        return view('applicant.profile', compact('applicant'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'nationality' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'summary' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'portfolio_url' => 'nullable|url',
        ]);

        $applicant = auth()->user()->applicant;
        if (!$applicant) {
            $applicant = Applicant::create(array_merge($data, ['user_id' => auth()->id(), 'email' => auth()->user()->email, 'status' => 'active', 'source' => 'website']));
        } else {
            $applicant->update($data);
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $request->validate(['resume' => 'required|file|mimes:pdf,doc,docx|max:5120']);
            if ($applicant->resume_path) {
                Storage::disk('public')->delete($applicant->resume_path);
            }
            $path = $request->file('resume')->store('resumes/' . $applicant->id, 'public');
            $applicant->update(['resume_path' => $path]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function jobs(Request $request)
    {
        $query = JobPosting::with('department', 'jobPosition')
            ->where('status', 'published')
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->department_id, fn($q, $d) => $q->where('department_id', $d));

        $jobs = $query->paginate(9);
        return view('applicant.jobs', compact('jobs'));
    }

    public function showJob(JobPosting $posting)
    {
        $posting->load('department', 'jobPosition');
        return view('applicant.job-show', compact('posting'));
    }

    public function apply(Request $request, JobPosting $posting)
    {
        $applicant = auth()->user()->applicant;
        if (!$applicant) {
            return redirect()->route('applicant.profile')->with('error', 'Please complete your candidate profile before submitting an application.');
        }

        $request->validate([
            'cover_letter' => 'nullable|string',
            'custom_notes' => 'nullable|string|max:1000',
            'custom_resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'screening_answers' => 'nullable|array',
        ]);

        try {
            $service = app(\App\Services\ApplicationSubmissionService::class);
            $application = $service->submit($request, $posting, $applicant);

            return redirect()->route('applicant.track')->with('success', "Application submitted successfully! Your tracking reference code is: {$application->reference_code}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function track()
    {
        $applicant = auth()->user()->applicant;
        $applications = $applicant ? Application::with([
                'jobPosting.department',
                'aiRecommendation',
                'interviews',
                'offerLetter',
            ])
            ->where('applicant_id', $applicant->id)
            ->latest()
            ->get() : collect();
        return view('applicant.track', compact('applications'));
    }

    public function withdrawApplication(Application $application)
    {
        $this->authorizeApplicant($application);
        $application->update(['status' => 'withdrawn']);
        return back()->with('success', 'Application withdrawn.');
    }

    public function acceptOffer(OfferLetter $offer)
    {
        $this->authorizeOffer($offer);
        $offer->update(['status' => 'accepted', 'response_at' => now()]);
        $offer->application->update(['status' => 'hired']);

        // Invalidate the current session and redirect to login with a welcome message.
        // The applicant's portal access ends here; HR will set up their employee account.
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', '🎉 Congratulations! You have accepted the offer. Welcome aboard! Please wait for HR to set up your employee account.');
    }

    public function rejectOffer(Request $request, OfferLetter $offer)
    {
        $this->authorizeOffer($offer);
        $offer->update([
            'status' => 'rejected',
            'response_at' => now(),
            'response_notes' => $request->response_notes,
        ]);
        $offer->application->update(['status' => 'rejected']);
        return back()->with('success', 'Offer declined.');
    }

    public function uploadDocument(Request $request)
    {
        $applicant = auth()->user()->applicant;
        if (!$applicant) return back()->with('error', 'Complete your profile first.');

        $data = $request->validate([
            'document_type' => 'required|in:resume,diploma,transcript,certificate,government_id,contract,other',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $applicant->id, 'public');

        UploadedDocument::create([
            'applicant_id' => $applicant->id,
            'application_id' => $request->application_id,
            'document_type' => $data['document_type'],
            'document_name' => $data['document_name'],
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Document uploaded for verification.');
    }

    public function notifications()
    {
        $notifications = NotificationRecord::where('user_id', auth()->id())->latest()->paginate(20);
        return view('applicant.notifications', compact('notifications'));
    }

public function markNotificationRead(NotificationRecord $notification)
    {
        $notification->update(['is_read' => true]);
        return back();
    }

    public function markAllNotificationsRead()
    {
        NotificationRecord::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    protected function authorizeApplicant(Application $application)
    {
        $applicant = auth()->user()->applicant;
        abort_unless($applicant && $application->applicant_id === $applicant->id, 403);
    }

    protected function authorizeOffer(OfferLetter $offer)
    {
        $applicant = auth()->user()->applicant;
        abort_unless($applicant && $offer->application->applicant_id === $applicant->id, 403);
    }

    // --- Education Management ---
    public function storeEducation(Request $request)
    {
        $applicant = $this->getOrCreateApplicant();
        $data = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'gpa' => 'nullable|numeric|between:0,4.00',
            'honors' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $applicant->education()->create($data);
        return back()->with('success', 'Education entry added successfully.');
    }

    public function destroyEducation(ApplicantEducation $education)
    {
        $applicant = auth()->user()->applicant;
        abort_unless($applicant && $education->applicant_id === $applicant->id, 403);
        $education->delete();
        return back()->with('success', 'Education entry deleted.');
    }

    // --- Experience Management ---
    public function storeExperience(Request $request)
    {
        $applicant = $this->getOrCreateApplicant();
        $data = $request->validate([
            'company' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        $data['is_current'] = $request->has('is_current');

        $applicant->experiences()->create($data);
        return back()->with('success', 'Work experience entry added successfully.');
    }

    public function destroyExperience(ApplicantExperience $experience)
    {
        $applicant = auth()->user()->applicant;
        abort_unless($applicant && $experience->applicant_id === $applicant->id, 403);
        $experience->delete();
        return back()->with('success', 'Work experience entry deleted.');
    }

    // --- Skills Management ---
    public function storeSkill(Request $request)
    {
        $applicant = $this->getOrCreateApplicant();
        $data = $request->validate([
            'skill' => 'required|string|max:255',
            'proficiency' => 'nullable|string|in:Beginner,Intermediate,Advanced,Expert',
            'years_of_experience' => 'nullable|integer|min:0|max:50',
        ]);

        $applicant->skills()->create($data);
        return back()->with('success', 'Skill added successfully.');
    }

    public function destroySkill(ApplicantSkill $skill)
    {
        $applicant = auth()->user()->applicant;
        abort_unless($applicant && $skill->applicant_id === $applicant->id, 403);
        $skill->delete();
        return back()->with('success', 'Skill removed.');
    }

    // --- Certification Management ---
    public function storeCertification(Request $request)
    {
        $applicant = $this->getOrCreateApplicant();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'credential_id' => 'nullable|string|max:255',
            'credential_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $applicant->certifications()->create($data);
        return back()->with('success', 'Certification added successfully.');
    }

    public function destroyCertification(Certification $certification)
    {
        $applicant = auth()->user()->applicant;
        abort_unless($applicant && $certification->applicant_id === $applicant->id, 403);
        $certification->delete();
        return back()->with('success', 'Certification removed.');
    }

    protected function getOrCreateApplicant()
    {
        $applicant = auth()->user()->applicant;
        if (!$applicant) {
            $user = auth()->user();
            $nameParts = explode(' ', $user->name ?? 'Candidate', 2);
            $applicant = Applicant::create([
                'user_id' => $user->id,
                'first_name' => $nameParts[0] ?? 'Candidate',
                'last_name' => $nameParts[1] ?? '',
                'email' => $user->email,
                'status' => 'active',
                'source' => 'website',
            ]);
        }
        return $applicant;
    }
}

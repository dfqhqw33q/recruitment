<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecord;
use App\Models\Onboarding;
use App\Models\UploadedDocument;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    private function employeeProfile()
    {
        return auth()->user()->employeeProfile ?? null;
    }

    public function dashboard()
    {
        $user = auth()->user();
        $profile = $this->employeeProfile();
        
        $applicant = $user->applicant 
            ?? ($profile?->applicant_id ? \App\Models\Applicant::find($profile->applicant_id) : \App\Models\Applicant::where('user_id', $user->id)->first());

        $application = null;
        if ($applicant) {
            $application = \App\Models\Application::where('applicant_id', $applicant->id)
                ->with(['jobPosting.department', 'onboarding'])
                ->whereIn('status', ['hired', 'offered', 'interviewed', 'screening', 'submitted'])
                ->orderByRaw("FIELD(status, 'hired', 'offered', 'interviewed', 'screening', 'submitted')")
                ->latest()
                ->first() 
                ?? \App\Models\Application::where('applicant_id', $applicant->id)
                ->with(['jobPosting.department', 'onboarding'])
                ->latest()
                ->first();
        }

        $appliedJobTitle = $application?->jobPosting?->title 
            ?? $profile?->jobPosition?->title 
            ?? $profile?->position 
            ?? 'N/A';

        $departmentName = $application?->jobPosting?->department?->name 
            ?? $profile?->department?->name 
            ?? 'General';

        $onboarding = $application?->onboarding 
            ?? ($applicant ? Onboarding::whereHas('application', function ($q) use ($applicant) {
                $q->where('applicant_id', $applicant->id);
            })->latest()->first() : null);

        $notifications = NotificationRecord::where('user_id', $user->id)->latest()->limit(5)->get();
        $unreadCount = NotificationRecord::where('user_id', $user->id)->where('is_read', false)->count();

        return view('employee.dashboard', compact(
            'profile', 'applicant', 'application', 'appliedJobTitle', 'departmentName', 'onboarding', 'notifications', 'unreadCount'
        ));
    }

    public function profile()
    {
        $profile = $this->employeeProfile();
        return view('employee.profile', compact('profile'));
    }

    public function onboarding()
    {
        $user = auth()->user();
        $profile = $this->employeeProfile();
        
        $applicant = $user->applicant 
            ?? ($profile?->applicant_id ? \App\Models\Applicant::find($profile->applicant_id) : \App\Models\Applicant::where('user_id', $user->id)->first());

        $applicantId = $profile?->applicant_id ?? $applicant?->id;

        $onboarding = Onboarding::with('assignedOfficer', 'application.jobPosting')
            ->where(function ($q) use ($applicantId, $user) {
                if ($applicantId) {
                    $q->whereHas('application', function ($sub) use ($applicantId) {
                        $sub->where('applicant_id', $applicantId);
                    });
                }
                $q->orWhere('employee_id', $user->id);
            })
            ->latest()
            ->first();

        return view('employee.onboarding', compact('profile', 'onboarding'));
    }

    public function documents()
    {
        $user = auth()->user();
        $profile = $this->employeeProfile();
        $documents = collect();
        if ($profile && $profile->applicant_id) {
            $documents = UploadedDocument::where('applicant_id', $profile->applicant_id)
                ->orWhere('employee_id', $user->id)
                ->latest()
                ->get();
        }
        return view('employee.documents', compact('profile', 'documents'));
    }

    public function notifications()
    {
        $notifications = NotificationRecord::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        NotificationRecord::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('employee.notifications', compact('notifications'));
    }

    public function markNotificationRead(NotificationRecord $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->update(['is_read' => true]);
        }
        return back();
    }

    public function markAllNotificationsRead()
    {
        NotificationRecord::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
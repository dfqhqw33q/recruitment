<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AiRecommendationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApplicantDashboardController;
use App\Http\Controllers\ApplicantPortalController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferLetterController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeePortalController;
use Illuminate\Support\Facades\Route;

// Public Travel & Tours website
Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::get('/about', [PublicSiteController::class, 'about'])->name('public.about');
Route::get('/tours', [PublicSiteController::class, 'tours'])->name('public.tours');
Route::get('/destinations', [PublicSiteController::class, 'destinations'])->name('public.destinations');
Route::get('/careers', [PublicSiteController::class, 'careers'])->name('public.careers');
Route::get('/careers/{posting}', [PublicSiteController::class, 'showJob'])->name('public.jobs.show');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('public.contact');
Route::post('/contact', [PublicSiteController::class, 'submitContact'])->name('public.contact.submit');

// Guest / Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:login');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get')->middleware('auth');

// Shared authenticated routes (notifications for all)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

// Applicant Portal (Applicant role only)
Route::middleware(['auth', 'role:Applicant'])->prefix('applicant')->name('applicant.')->group(function () {
    Route::get('/dashboard', [ApplicantDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ApplicantPortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [ApplicantPortalController::class, 'updateProfile'])->name('profile.update');
    Route::post('/resume/parse-autofill', [ApplicantPortalController::class, 'parseAndAutoFillResume'])->name('resume.parse-autofill');
    Route::get('/resume/preview', [ApplicantPortalController::class, 'previewResume'])->name('resume.preview');
    Route::get('/jobs', [ApplicantPortalController::class, 'jobs'])->name('jobs');
    Route::get('/jobs/{posting}', [ApplicantPortalController::class, 'showJob'])->name('jobs.show');
    Route::post('/jobs/{posting}/apply', [ApplicantPortalController::class, 'apply'])->name('apply');
    Route::get('/applications', [ApplicantPortalController::class, 'track'])->name('applications');
    Route::get('/track', [ApplicantPortalController::class, 'track'])->name('track');
    Route::get('/applications/{application}/resume/preview', [ApplicantPortalController::class, 'previewApplicationResume'])->name('applications.resume.preview');
    Route::post('/applications/{application}/withdraw', [ApplicantPortalController::class, 'withdrawApplication'])->name('applications.withdraw');
    Route::post('/offers/{offer}/accept', [ApplicantPortalController::class, 'acceptOffer'])->name('offers.accept');
    Route::post('/offers/{offer}/reject', [ApplicantPortalController::class, 'rejectOffer'])->name('offers.reject');
    Route::post('/documents', [ApplicantPortalController::class, 'uploadDocument'])->name('documents.store');
    Route::get('/documents/{document}/preview', [ApplicantPortalController::class, 'previewDocument'])->name('documents.preview');

    // Education, Experience, Skills, Certifications management
    Route::post('/education', [ApplicantPortalController::class, 'storeEducation'])->name('education.store');
    Route::delete('/education/{education}', [ApplicantPortalController::class, 'destroyEducation'])->name('education.destroy');

    Route::post('/experience', [ApplicantPortalController::class, 'storeExperience'])->name('experience.store');
    Route::delete('/experience/{experience}', [ApplicantPortalController::class, 'destroyExperience'])->name('experience.destroy');

    Route::post('/skill', [ApplicantPortalController::class, 'storeSkill'])->name('skills.store');
    Route::delete('/skill/{skill}', [ApplicantPortalController::class, 'destroySkill'])->name('skills.destroy');

    Route::post('/certification', [ApplicantPortalController::class, 'storeCertification'])->name('certifications.store');
    Route::delete('/certification/{certification}', [ApplicantPortalController::class, 'destroyCertification'])->name('certifications.destroy');

    Route::get('/notifications', [ApplicantPortalController::class, 'notifications'])->name('notifications');
    Route::patch('/notifications/{notification}/read', [ApplicantPortalController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [ApplicantPortalController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
});

// Employee Portal (Employee role only — separate from Applicant portal)
Route::middleware(['auth', 'role:Employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [EmployeePortalController::class, 'profile'])->name('profile');
    Route::get('/onboarding', [EmployeePortalController::class, 'onboarding'])->name('onboarding');
    Route::get('/documents', [EmployeePortalController::class, 'documents'])->name('documents');
    Route::get('/notifications', [EmployeePortalController::class, 'notifications'])->name('notifications');
    Route::patch('/notifications/{notification}/read', [EmployeePortalController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [EmployeePortalController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
});

// HR / Staff Dashboard - accessible to HR staff only (Employees have their own portal)
Route::middleware(['auth', 'role:Super Admin,HR Administrator,Recruitment Officer,Department Head'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Recruitment management
    Route::resource('recruitment/job-postings', JobPostingController::class)
        ->parameters(['job-postings' => 'posting'])
        ->names([
            'index' => 'recruitment.job-postings.index',
            'create' => 'recruitment.job-postings.create',
            'store' => 'recruitment.job-postings.store',
            'show' => 'recruitment.job-postings.show',
            'edit' => 'recruitment.job-postings.edit',
            'update' => 'recruitment.job-postings.update',
            'destroy' => 'recruitment.job-postings.destroy',
        ]);
    Route::post('recruitment/job-postings/{posting}/toggle-status', [JobPostingController::class, 'toggleStatus'])->name('recruitment.job-postings.toggle-status');

    Route::get('recruitment/applications', [ApplicationController::class, 'index'])->name('recruitment.applications.index');
    Route::get('recruitment/applications/{application}', [ApplicationController::class, 'show'])->name('recruitment.applications.show');
    Route::get('recruitment/applications/{application}/resume/preview', [ApplicationController::class, 'previewResume'])->name('recruitment.applications.resume.preview');
    Route::get('recruitment/applications/{application}/resume/download', [ApplicationController::class, 'downloadResume'])->name('recruitment.applications.resume.download');
    Route::patch('recruitment/applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('recruitment.applications.status');
    Route::post('recruitment/applications/{application}/shortlist', [ApplicationController::class, 'shortlist'])->name('recruitment.applications.shortlist');
    Route::post('recruitment/applications/{application}/reject', [ApplicationController::class, 'reject'])->name('recruitment.applications.reject');
    Route::post('recruitment/applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('recruitment.applications.withdraw');

    Route::post('recruitment/applications/bulk-action', [ApplicationController::class, 'bulkAction'])->name('recruitment.applications.bulk-action');

    // Interviews
    Route::get('recruitment/interviews', [InterviewController::class, 'index'])->name('recruitment.interviews.index');
    Route::get('recruitment/interviews/create', [InterviewController::class, 'create'])->name('recruitment.interviews.create');
    Route::post('recruitment/interviews', [InterviewController::class, 'store'])->name('recruitment.interviews.store');
    Route::get('recruitment/interviews/{interview}', [InterviewController::class, 'show'])->name('recruitment.interviews.show');
    Route::get('recruitment/interviews/{interview}/edit', [InterviewController::class, 'edit'])->name('recruitment.interviews.edit');
    Route::put('recruitment/interviews/{interview}', [InterviewController::class, 'update'])->name('recruitment.interviews.update');
    Route::delete('recruitment/interviews/{interview}', [InterviewController::class, 'destroy'])->name('recruitment.interviews.destroy');
    Route::get('recruitment/calendar', [InterviewController::class, 'calendar'])->name('recruitment.calendar');

    // Assessments
    Route::get('recruitment/interviews/{interview}/assessment', [AssessmentController::class, 'create'])->name('recruitment.interviews.assessment');
    Route::post('recruitment/interviews/{interview}/assessment', [AssessmentController::class, 'store'])->name('recruitment.interviews.assessment.store');

    // AI Recommendations
    Route::get('recruitment/ai', [AiRecommendationController::class, 'index'])->name('recruitment.ai.index');
    Route::get('recruitment/ai/{recommendation}', [AiRecommendationController::class, 'show'])->name('recruitment.ai.show');
    Route::post('recruitment/ai/generate/{application}', [AiRecommendationController::class, 'generate'])->name('recruitment.ai.generate');
    Route::post('recruitment/ai/generate-posting/{posting}', [AiRecommendationController::class, 'generateForPosting'])->name('recruitment.ai.generate-posting');
    Route::post('recruitment/ai/generate-all', [AiRecommendationController::class, 'generateAll'])->name('recruitment.ai.generate-all');

    // Offers
    Route::get('recruitment/offers', [OfferLetterController::class, 'index'])->name('recruitment.offers.index');
    Route::get('recruitment/offers/create', [OfferLetterController::class, 'create'])->name('recruitment.offers.create');
    Route::post('recruitment/offers', [OfferLetterController::class, 'store'])->name('recruitment.offers.store');
    Route::get('recruitment/offers/{offer}', [OfferLetterController::class, 'show'])->name('recruitment.offers.show');
    Route::put('recruitment/offers/{offer}', [OfferLetterController::class, 'update'])->name('recruitment.offers.update');
    Route::post('recruitment/offers/{offer}/send', [OfferLetterController::class, 'send'])->name('recruitment.offers.send');
    Route::post('recruitment/offers/{offer}/respond', [OfferLetterController::class, 'respond'])->name('recruitment.offers.respond');
    Route::delete('recruitment/offers/{offer}', [OfferLetterController::class, 'destroy'])->name('recruitment.offers.destroy');

    // Onboarding
    Route::get('recruitment/onboarding', [OnboardingController::class, 'index'])->name('recruitment.onboarding.index');
    Route::get('recruitment/onboarding/create', [OnboardingController::class, 'create'])->name('recruitment.onboarding.create');
    Route::post('recruitment/onboarding', [OnboardingController::class, 'store'])->name('recruitment.onboarding.store');
    Route::get('recruitment/onboarding/{onboarding}', [OnboardingController::class, 'show'])->name('recruitment.onboarding.show');
    Route::put('recruitment/onboarding/{onboarding}', [OnboardingController::class, 'update'])->name('recruitment.onboarding.update');
    Route::post('recruitment/onboarding/{onboarding}/checklist', [OnboardingController::class, 'updateChecklist'])->name('recruitment.onboarding.checklist');
    Route::post('recruitment/onboarding/{onboarding}/employee-profile', [OnboardingController::class, 'createEmployeeProfile'])->name('recruitment.onboarding.employee-profile');
    Route::delete('recruitment/onboarding/{onboarding}', [OnboardingController::class, 'destroy'])->name('recruitment.onboarding.destroy');

    // Documents
    Route::get('recruitment/documents', [DocumentController::class, 'index'])->name('recruitment.documents.index');
    Route::post('recruitment/documents', [DocumentController::class, 'store'])->name('recruitment.documents.store');
    Route::match(['post', 'patch'], 'recruitment/documents/{document}/verify', [DocumentController::class, 'verify'])->name('recruitment.documents.verify');
    Route::get('recruitment/documents/{document}/preview', [DocumentController::class, 'preview'])->name('recruitment.documents.preview');
    Route::get('recruitment/documents/{document}/download', [DocumentController::class, 'download'])->name('recruitment.documents.download');
    Route::delete('recruitment/documents/{document}', [DocumentController::class, 'destroy'])->name('recruitment.documents.destroy');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/candidates', [ReportController::class, 'candidates'])->name('reports.candidates');
    Route::get('reports/hiring', [ReportController::class, 'hiring'])->name('reports.hiring');
    Route::get('reports/recruitment-summary', [ReportController::class, 'recruitmentSummary'])->name('reports.recruitment-summary');
});

// Admin-only resources
Route::middleware(['auth', 'role:Super Admin,HR Administrator'])->group(function () {
    Route::get('admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');

    Route::get('admin/departments', [DepartmentController::class, 'index'])->name('admin.departments.index');
    Route::post('admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');
    Route::put('admin/departments/{department}', [DepartmentController::class, 'update'])->name('admin.departments.update');
    Route::delete('admin/departments/{department}', [DepartmentController::class, 'destroy'])->name('admin.departments.destroy');

    Route::get('admin/job-positions', [JobPositionController::class, 'index'])->name('admin.job-positions.index');
    Route::post('admin/job-positions', [JobPositionController::class, 'store'])->name('admin.job-positions.store');
    Route::put('admin/job-positions/{position}', [JobPositionController::class, 'update'])->name('admin.job-positions.update');
    Route::delete('admin/job-positions/{position}', [JobPositionController::class, 'destroy'])->name('admin.job-positions.destroy');

    Route::get('admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
});

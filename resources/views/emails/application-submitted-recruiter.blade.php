<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Job Application</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #0f172a; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">New Application Received</h2>
        </div>
        <div style="padding: 24px;">
            <p>Hello Recruitment Team,</p>
            <p>A new candidate has submitted an application for <strong>{{ $application->jobPosting->title }}</strong>.</p>
            
            <div style="background-color: #f8fafc; border-left: 4px solid #0f172a; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 4px 0; font-size: 14px; color: #64748b;">Applicant Name:</p>
                <p style="margin: 0 0 12px 0; font-size: 18px; font-weight: bold; color: #0f172a;">{{ $application->applicant->full_name }} ({{ $application->applicant->email }})</p>
                <p style="margin: 0 0 4px 0; font-size: 14px; color: #64748b;">Reference Code:</p>
                <p style="margin: 0; font-size: 16px; font-weight: bold; color: #4f46e5; font-family: monospace;">{{ $application->reference_code }}</p>
            </div>

            <p><strong>Candidate Summary:</strong></p>
            <ul>
                <li><strong>Position:</strong> {{ $application->jobPosting->title }}</li>
                <li><strong>Experience:</strong> {{ $application->applicant->total_years_experience ?? 0 }} years</li>
                <li><strong>Submitted At:</strong> {{ $application->applied_at ? $application->applied_at->format('M d, Y g:i A') : now()->format('M d, Y g:i A') }}</li>
                @if($application->aiRecommendation)
                <li><strong>Initial AI Match Score:</strong> <span style="color: #16a34a; font-weight: bold;">{{ $application->aiRecommendation->match_score }}%</span></li>
                @endif
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('applications.show', $application) }}" style="background-color: #0f172a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Review Candidate Application</a>
            </div>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            System Notification &bull; {{ config('app.name', 'Recruitment System') }}
        </div>
    </div>
</body>
</html>

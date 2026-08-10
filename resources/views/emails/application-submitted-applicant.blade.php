<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application Received</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #4f46e5; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">Application Received</h2>
        </div>
        <div style="padding: 24px;">
            <p>Dear {{ $application->applicant->first_name }},</p>
            <p>Thank you for applying for the position of <strong>{{ $application->jobPosting->title }}</strong> at {{ config('app.name', 'Recruitment System') }}.</p>
            
            <div style="background-color: #f8fafc; border-left: 4px solid #4f46e5; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 8px 0; font-size: 14px; color: #64748b;">Application Tracking Reference:</p>
                <p style="margin: 0; font-size: 20px; font-weight: bold; color: #4f46e5; font-family: monospace;">{{ $application->reference_code }}</p>
            </div>

            <p><strong>Position Details:</strong></p>
            <ul>
                <li><strong>Role:</strong> {{ $application->jobPosting->title }}</li>
                <li><strong>Department:</strong> {{ $application->jobPosting->department->name ?? 'N/A' }}</li>
                <li><strong>Location:</strong> {{ $application->jobPosting->location ?? 'On-site' }}</li>
                <li><strong>Date Submitted:</strong> {{ $application->applied_at ? $application->applied_at->format('M d, Y g:i A') : now()->format('M d, Y g:i A') }}</li>
            </ul>

            <p>Our recruitment team is currently reviewing your profile against the job requirements. You can track your application status anytime through your candidate portal dashboard.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('applicant.track') }}" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Track Application Status</a>
            </div>

            <p>Best regards,<br><strong>Talent Acquisition Team</strong><br>{{ config('app.name', 'Recruitment System') }}</p>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            This is an automated notification. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>

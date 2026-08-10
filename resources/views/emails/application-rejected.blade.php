<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #475569; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">Application Status Update</h2>
        </div>
        <div style="padding: 24px;">
            <p>Dear {{ $application->applicant->first_name }},</p>
            <p>Thank you for taking the time to apply for the position of <strong>{{ $application->jobPosting->title }}</strong> (Ref: <code>{{ $application->reference_code }}</code>) at {{ config('app.name', 'our company') }}.</p>
            
            <p>After careful consideration of all applications received, we regret to inform you that we have decided to move forward with other candidates whose qualifications more closely align with the requirements of this specific role at this time.</p>

            @if($application->rejection_reason)
            <div style="background-color: #f8fafc; border-left: 4px solid #64748b; padding: 12px; margin: 15px 0; border-radius: 4px; font-size: 13px; color: #475569;">
                <strong>Feedback:</strong> {{ $application->rejection_reason }}
            </div>
            @endif

            <p>We truly appreciate your interest in joining our team and wish you all the best in your job search and professional endeavors.</p>

            <p>Sincerely,<br><strong>Talent Acquisition Team</strong><br>{{ config('app.name', 'Recruitment System') }}</p>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            Notification &bull; {{ config('app.name', 'Recruitment System') }}
        </div>
    </div>
</body>
</html>

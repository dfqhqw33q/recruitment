<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application Shortlisted</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #16a34a; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">Congratulations! You're Shortlisted</h2>
        </div>
        <div style="padding: 24px;">
            <p>Dear {{ $application->applicant->first_name }},</p>
            <p>Great news! We are pleased to inform you that your application for <strong>{{ $application->jobPosting->title }}</strong> (Ref: <code>{{ $application->reference_code }}</code>) has been <strong>shortlisted</strong> by our hiring team.</p>
            
            <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-size: 14px; color: #166534;">Our talent acquisition team was impressed by your experience and qualifications. We will reach out shortly regarding the next step in our selection process.</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('applicant.track') }}" style="background-color: #16a34a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">View Application Progress</a>
            </div>

            <p>Best regards,<br><strong>Talent Acquisition Team</strong><br>{{ config('app.name', 'Recruitment System') }}</p>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            Notification &bull; {{ config('app.name', 'Recruitment System') }}
        </div>
    </div>
</body>
</html>

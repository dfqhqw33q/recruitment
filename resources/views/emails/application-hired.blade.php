<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome Aboard</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #047857; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">Welcome Aboard! Official Hire Confirmation</h2>
        </div>
        <div style="padding: 24px;">
            <p>Dear {{ $application->applicant->first_name }},</p>
            <p>We are excited to confirm that you have been officially <strong>hired</strong> for the position of <strong>{{ $application->jobPosting->title }}</strong>!</p>
            
            <div style="background-color: #ecfdf5; border-left: 4px solid #047857; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; font-size: 14px; color: #065f46;">Our onboarding team will be in touch shortly to guide you through your orientation checklist, document verification, and official employee setup.</p>
            </div>

            <p>Welcome to {{ config('app.name', 'our team') }}!</p>

            <p>Best regards,<br><strong>Human Resources & Onboarding Team</strong><br>{{ config('app.name', 'Recruitment System') }}</p>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            Notification &bull; {{ config('app.name', 'Recruitment System') }}
        </div>
    </div>
</body>
</html>

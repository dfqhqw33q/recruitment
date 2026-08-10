<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Interview Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #9333ea; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">Interview Invitation</h2>
        </div>
        <div style="padding: 24px;">
            <p>Dear {{ $application->applicant->first_name }},</p>
            <p>You have been invited to an interview for the position of <strong>{{ $application->jobPosting->title }}</strong> (Ref: <code>{{ $application->reference_code }}</code>).</p>
            
            @if($interview)
            <div style="background-color: #faf5ff; border-left: 4px solid #9333ea; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #581c87;">Interview Details:</p>
                <ul style="margin: 0; padding-left: 20px; color: #6b21a8; font-size: 14px;">
                    <li><strong>Type:</strong> {{ ucfirst($interview->type) }} Interview (Round {{ $interview->round }})</li>
                    <li><strong>Scheduled Time:</strong> {{ \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y \a\t g:i A') }}</li>
                    <li><strong>Duration:</strong> {{ $interview->duration_minutes }} minutes</li>
                    @if($interview->location)
                    <li><strong>Location:</strong> {{ $interview->location }}</li>
                    @endif
                    @if($interview->meeting_link)
                    <li><strong>Meeting Link:</strong> <a href="{{ $interview->meeting_link }}" style="color:#9333ea;">{{ $interview->meeting_link }}</a></li>
                    @endif
                </ul>
            </div>
            @endif

            <p>Please log in to your candidate portal to view details and confirm your attendance.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('applicant.track') }}" style="background-color: #9333ea; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Track & Schedule Interview</a>
            </div>

            <p>Best regards,<br><strong>Talent Acquisition Team</strong><br>{{ config('app.name', 'Recruitment System') }}</p>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            Notification &bull; {{ config('app.name', 'Recruitment System') }}
        </div>
    </div>
</body>
</html>

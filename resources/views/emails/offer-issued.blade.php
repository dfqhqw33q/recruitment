<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Offer Letter</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f6f8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background-color: #059669; padding: 20px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0;">Official Job Offer Letter</h2>
        </div>
        <div style="padding: 24px;">
            <p>Dear {{ $offer->application->applicant->first_name }},</p>
            <p>We are thrilled to extend an official job offer for the position of <strong>{{ $offer->jobPosting->title }}</strong> at {{ config('app.name', 'Recruitment System') }}!</p>
            
            <div style="background-color: #ecfdf5; border-left: 4px solid #059669; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #065f46;">Offer Summary:</p>
                <ul style="margin: 0; padding-left: 20px; color: #047857; font-size: 14px;">
                    <li><strong>Offer Number:</strong> {{ $offer->offer_number }}</li>
                    <li><strong>Position:</strong> {{ $offer->jobPosting->title }}</li>
                    <li><strong>Offered Salary:</strong> ₱{{ number_format($offer->salary, 2) }}</li>
                    <li><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($offer->start_date)->format('M d, Y') }}</li>
                </ul>
            </div>

            <p>Please review the complete terms and accept or decline your offer in your candidate portal.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('applicant.track') }}" style="background-color: #059669; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Review & Sign Offer Letter</a>
            </div>

            <p>Congratulations and welcome to the team!<br><strong>HR Management Team</strong><br>{{ config('app.name', 'Recruitment System') }}</p>
        </div>
        <div style="background-color: #f1f5f9; padding: 12px; text-align: center; font-size: 12px; color: #64748b;">
            Notification &bull; {{ config('app.name', 'Recruitment System') }}
        </div>
    </div>
</body>
</html>

<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\OfferLetter;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferLetterController extends Controller
{
    public function index(Request $request)
    {
        $query = OfferLetter::with('application.applicant', 'jobPosting', 'preparer')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest();

        $offers = $query->paginate(15);
        return view('recruitment.offers.index', compact('offers'));
    }

    public function create(Request $request)
    {
        $applications = Application::with('applicant', 'jobPosting')
            ->whereIn('status', ['recommended', 'assessed'])
            ->doesntHave('offerLetter')
            ->get();
        return view('recruitment.offers.create', compact('applications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id|unique:offer_letters,application_id',
            'salary' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'terms' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        $application = Application::with('jobPosting')->find($data['application_id']);
        $data['job_posting_id'] = $application->job_posting_id;
        $data['prepared_by'] = auth()->id();
        $data['offer_number'] = 'OFF-' . strtoupper(Str::random(8));
        $data['status'] = 'draft';

        $offer = OfferLetter::create($data);

        app(ActivityLogService::class)->log(
            'create', 'Offers',
            "Offer letter {$offer->offer_number} created for application #{$application->id}.",
            'OfferLetter', $offer->id
        );

        return redirect()->route('recruitment.offers.show', $offer)
            ->with('success', 'Offer letter created.');
    }

    public function show(OfferLetter $offer)
    {
        $offer->load(['application.applicant', 'jobPosting', 'preparer']);
        return view('recruitment.offers.show', compact('offer'));
    }

    public function update(Request $request, OfferLetter $offer)
    {
        $data = $request->validate([
            'salary' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'terms' => 'nullable|string',
            'benefits' => 'nullable|string',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
        ]);

        $offer->update($data);
        return redirect()->route('recruitment.offers.show', $offer)->with('success', 'Offer updated.');
    }

    public function send(OfferLetter $offer)
    {
        $offer->update(['status' => 'sent', 'sent_at' => now()]);
        $offer->application->update(['status' => 'offer_sent']);

        app(ActivityLogService::class)->log(
            'send', 'Offers',
            "Offer {$offer->offer_number} sent.",
            'OfferLetter', $offer->id
        );

        // Notify applicant of offer letter via email + in-app notification
        try {
            $offer->load('application.applicant', 'jobPosting');
            app(\App\Services\ApplicationStageNotificationService::class)->notifyOfferSent($offer);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('Offer notification skipped: ' . $e->getMessage());
        }

        return back()->with('success', 'Offer sent to applicant and notification delivered.');
    }

    public function respond(Request $request, OfferLetter $offer)
    {
        $data = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'response_notes' => 'nullable|string',
        ]);

        $offer->update([
            'status' => $data['status'],
            'response_at' => now(),
            'response_notes' => $data['response_notes'] ?? null,
        ]);

        $offer->application->update(['status' => $data['status'] === 'accepted' ? 'hired' : 'rejected']);

        app(ActivityLogService::class)->log(
            'respond', 'Offers',
            "Offer {$offer->offer_number} was {$data['status']}.",
            'OfferLetter', $offer->id
        );

        return back()->with('success', "Offer {$data['status']}. Application updated.");
    }

    public function destroy(OfferLetter $offer)
    {
        $offer->delete();
        return redirect()->route('recruitment.offers.index')->with('success', 'Offer deleted.');
    }
}

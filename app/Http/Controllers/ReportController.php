<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\OfferLetter;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function candidates(Request $request)
    {
        $query = Application::with('applicant', 'jobPosting', 'aiRecommendation')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->job_posting_id, fn($q, $j) => $q->where('job_posting_id', $j));

        $applications = $query->get();

        if ($request->format === 'csv') {
            return $this->candidatesCsv($applications);
        }

        $pdf = Pdf::loadView('reports.pdf.candidates', compact('applications'));
        return $pdf->download('candidate-report-' . now()->format('Y-m-d') . '.pdf');
    }

    protected function candidatesCsv($applications)
    {
        $filename = 'candidates-' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Candidate', 'Email', 'Position', 'Status', 'Applied At', 'AI Match', 'AI Recommendation']);

        foreach ($applications as $app) {
            fputcsv($handle, [
                $app->applicant?->full_name,
                $app->applicant?->email,
                $app->jobPosting?->title,
                $app->status,
                $app->applied_at?->format('Y-m-d'),
                $app->aiRecommendation?->match_score . '%',
                str_replace('_', ' ', ucwords($app->aiRecommendation?->recommendation ?? '')),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function hiring(Request $request)
    {
        $hired = Application::where('status', 'hired')->with('applicant', 'jobPosting')->get();

        if ($request->format === 'csv') {
            $filename = 'hired-report-' . now()->format('Y-m-d') . '.csv';
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, ['Employee', 'Email', 'Position', 'Hire Date', 'Dept']);
            foreach ($hired as $app) {
                fputcsv($handle, [
                    $app->applicant?->full_name,
                    $app->applicant?->email,
                    $app->jobPosting?->title,
                    $app->applied_at?->format('Y-m-d'),
                    $app->jobPosting?->department?->name,
                ]);
            }
            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);
            return Response::make($content, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"]);
        }

        $pdf = Pdf::loadView('reports.pdf.hiring', compact('hired'));
        return $pdf->download('hiring-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function recruitmentSummary()
    {
        $postings = JobPosting::withCount('applications')->with('department')->get();
        $totalApplicants = Applicant::count();
        $totalApplications = Application::count();
        $hired = Application::where('status', 'hired')->count();

        $pdf = Pdf::loadView('reports.pdf.recruitment-summary', compact('postings', 'totalApplicants', 'totalApplications', 'hired'));
        return $pdf->download('recruitment-summary-' . now()->format('Y-m-d') . '.pdf');
    }
}

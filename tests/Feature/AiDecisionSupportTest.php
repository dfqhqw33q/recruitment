<?php

namespace Tests\Feature;

use App\Models\AiPipelineInsight;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\AiInsightService;
use App\Services\AiProviderClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiDecisionSupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ai_single_recommendation_generation()
    {
        $hrUser = User::where('email', 'hr@hiraya.com')->first();
        $application = Application::first();

        $response = $this->actingAs($hrUser)->post(route('recruitment.ai.generate', ['application' => $application->id]));
        $response->assertRedirect();

        $this->assertDatabaseHas('ai_recommendations', [
            'application_id' => $application->id,
        ]);
    }

    public function test_ai_pipeline_insight_service_generates_insights()
    {
        /** @var AiInsightService $service */
        $service = app(AiInsightService::class);
        $insights = $service->generate();

        $this->assertIsArray($insights);
        $this->assertNotEmpty($insights);
        $this->assertDatabaseHas('ai_pipeline_insights', [
            'title' => $insights[0]['title'],
        ]);
    }

    public function test_dashboard_trigger_ai_insights_button()
    {
        $hrUser = User::where('email', 'hr@hiraya.com')->first();

        $response = $this->actingAs($hrUser)
            ->from(route('dashboard'))
            ->post(route('recruitment.ai.generate-all'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('show_ai_insights', true);

        // Subsequent GET request with session flag displays AI insights
        $dashboardResponse = $this->actingAs($hrUser)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('AI Recruitment Insights');
    }

    public function test_rule_based_fallback_when_openrouter_api_fails()
    {
        // Mock AiProviderClient to return null simulating API failure/timeout
        $this->mock(AiProviderClient::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('chat')->andReturn(null);
        });

        /** @var AiInsightService $service */
        $service = app(AiInsightService::class);
        $insights = $service->generate();

        // Must gracefully return rule-based fallback insights without crashing
        $this->assertIsArray($insights);
        $this->assertNotEmpty($insights);
    }
}

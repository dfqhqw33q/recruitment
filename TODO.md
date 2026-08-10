# AI-Assisted Recruitment Decision Support Integration

## Steps
- [x] Analyze existing rule-based AI recommendation engine & dashboard
- [x] Add AI provider config (`config/ai.php`, `config/services.php` openrouter block)
- [x] Add `OPENROUTER_API_KEY`, `AI_MODEL`, `AI_TIMEOUT`, `AI_JSON_MODE`, `AI_FALLBACK_ON_ERROR` to `.env`
- [x] Create `app/Services/AiProviderClient.php` (OpenRouter `/chat/completions` wrapper)
- [x] Create `app/Services/AiInsightService.php` (aggregate pipeline-insight service, single API call)
- [  x] Create `app/Models/AiPipelineInsight.php`
- [x] Create & run migration `2026_08_05_212070_create_ai_pipeline_insights_table.php`
- [x] Create & run migration `2026_08_05_212080_add_fields_to_ai_pipeline_insights_table.php`
- [x] Rewrite `AiRecommendationController::generateAll()` to call `AiInsightService` (single aggregate call — fixes 60s timeout)
- [x] Update `DashboardController` to query & pass `$pipelineInsights`
- [x] Redesign `resources/views/dashboard.blade.php` as decision-support layout (narrative insight cards, priority badges, evidence, impact, recommendation, "Why this insight?")
- [x] Fix `AiInsightService::buildPrompt()` array-interpolation bug
- [x] Verify syntax of all modified PHP files
- [x] Clear config/view/route caches
- [x] Verify both new migrations applied cleanly
- [x] Confirm AI insights persist in DB across page refreshes (verified 4 insights remain after re-query)
- [x] Add Cache-Control no-cache headers to dashboard response so a refresh always fetches fresh insights from the DB
- [x] Add `data_signature` + `generated_at` columns to `ai_pipeline_insights` (migration 2026_08_05_212090)
- [x] Add stale-detection + `regenerateIfStale()` to `AiInsightService`
- [x] **Manual-only behavior**: insights are cleared on a plain page refresh and only appear after clicking "Generate AI Insights" (removed auto-regeneration from `DashboardController::index()`; `generateAll()` sets a session flag so the redirect keeps them visible for that request)

## Notes
- AI insights are **manual-only**: they appear only after clicking "Generate AI Insights" and disappear on the next page refresh
- AI analyzes **aggregate** recruitment pipeline data only (never per-candidate rankings on dashboard)
- AI is a decision-**support** tool; final hiring decisions remain with HR
- Rule-based fallback engine remains in place when the AI API is unavailable

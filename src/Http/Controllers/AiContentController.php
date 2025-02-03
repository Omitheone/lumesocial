<?php

namespace LumeSocial\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use LumeSocial\Models\AiSettings;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Services\AI\ContentReviewer;
use LumeSocial\Jobs\GenerateAiContent;
use Illuminate\Support\Str;

class AiContentController extends Controller
{
    public function __construct(
        protected AiSettings $settings,
        protected ContentGenerator $generator
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
            'type' => 'required|string|in:post,comment,reply',
            'options' => 'sometimes|array',
        ]);

        $result = $this->generator->generate(
            $validated['prompt'],
            $validated['options'] ?? []
        );

        if (!$result) {
            return response()->json([
                'error' => 'Failed to generate content'
            ], 500);
        }

        return response()->json($result);
    }

    public function generateAsync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
            'type' => 'required|string|in:post,comment,reply',
            'options' => 'sometimes|array',
        ]);

        $jobId = Str::uuid()->toString();

        $job = new GenerateAiContent(
            $validated['prompt'],
            $this->settings,
            $validated['options'] ?? []
        );

        dispatch($job);

        return response()->json([
            'message' => 'Content generation started',
            'job_id' => $jobId
        ]);
    }

    public function review(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $reviewer = app(ContentReviewer::class);
        $result = $reviewer->review($validated['content']);

        if (!$result) {
            return response()->json([
                'error' => 'Failed to review content'
            ], 500);
        }

        return response()->json($result);
    }
} 
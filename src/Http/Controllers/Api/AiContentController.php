<?php

namespace LumeSocial\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LumeSocial\Http\Controllers\Controller;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Services\AI\ContentReviewer;
use LumeSocial\Models\AiSettings;
use LumeSocial\Jobs\GenerateAiContent;
use Illuminate\Support\Str;

class AiContentController extends Controller
{
    public function __construct(
        protected ContentGenerator $generator,
        protected AiSettings $settings
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

        return response()->json([
            'data' => $result,
            'status' => 'success'
        ]);
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
            'data' => [
                'job_id' => $jobId
            ],
            'message' => 'Content generation started',
            'status' => 'success'
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

        return response()->json([
            'data' => $result,
            'status' => 'success'
        ]);
    }

    public function status(Request $request, string $jobId): JsonResponse
    {
        // Add job status checking logic here
        return response()->json([
            'data' => [
                'job_id' => $jobId,
                'status' => 'pending' // or 'completed', 'failed', etc.
            ]
        ]);
    }
} 
<?php

namespace LumeSocial\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LumeSocial\Models\Post;
use LumeSocial\Models\AiSettings;
use LumeSocial\Services\AI\ContentReviewer;

class ReviewAiContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Post $post,
        protected AiSettings $settings,
        protected ?string $prompt = null
    ) {}

    public function handle(ContentReviewer $reviewer): void
    {
        $result = $reviewer->review($this->post->content);
        
        if ($result) {
            $this->post->update([
                'ai_review_score' => $result['score'],
                'ai_review_feedback' => $result['feedback'],
            ]);
        }
    }

    public function tags(): array
    {
        return [
            'ai_review',
            'post:'.$this->post->id,
            'org:'.$this->post->organization_id
        ];
    }

    public function retryAfter(): int
    {
        return 60; // Retry after 1 minute
    }

    public function failed(\Throwable $exception): void
    {
        $this->post->update([
            'ai_review' => [
                'error' => $exception->getMessage(),
                'failed_at' => now()->toIso8601String()
            ]
        ]);

        report($exception);
    }
} 
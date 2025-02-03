<?php

namespace LumeSocial\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LumeSocial\Models\AiSettings;
use LumeSocial\Services\AI\ContentGenerator;

class GenerateAiContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $prompt,
        protected AiSettings $settings,
        protected array $options = []
    ) {}

    public function handle(ContentGenerator $generator): array
    {
        return $generator->generate($this->prompt, $this->options);
    }
}

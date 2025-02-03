<?php

namespace LumeSocial\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LumeSocial\Services\AI\ImageSelector;

class SelectAiImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $content,
        protected int $count = 3
    ) {}

    public function handle(ImageSelector $selector): array
    {
        return $selector->selectImages($this->content, $this->count);
    }
} 
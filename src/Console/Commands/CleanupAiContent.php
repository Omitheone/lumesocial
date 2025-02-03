<?php

namespace LumeSocial\Console\Commands;

use Illuminate\Console\Command;
use LumeSocial\Models\AiContentHistory;

class CleanupAiContent extends Command
{
    protected $signature = 'ai:cleanup {--days=30}';
    protected $description = 'Clean up old AI content history';

    public function handle(): int
    {
        $days = $this->option('days');
        $deleted = AiContentHistory::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->info("Deleted {$deleted} old AI content records.");
        return Command::SUCCESS;
    }
} 
<?php

namespace LumeSocial\Console\Commands;

use Illuminate\Console\Command;
use LumeSocial\Models\Organization;
use LumeSocial\Services\Analytics\EngagementTracker;

class GenerateAiReport extends Command
{
    protected $signature = 'ai:report {organization?}';
    protected $description = 'Generate AI usage report';

    public function handle(EngagementTracker $tracker): int
    {
        $organizationId = $this->argument('organization');
        
        $query = Organization::query();
        if ($organizationId) {
            $query->where('id', $organizationId);
        }

        $organizations = $query->get();
        
        $organizations->each(function (Organization $organization) use ($tracker) {
            $metrics = $tracker->getMetricsForOrganization($organization);
            $this->info("Report for {$organization->name}:");
            $this->table(
                ['Metric', 'Value'],
                collect($metrics)->map(fn($v, $k) => [$k, $v])
            );
        });

        return Command::SUCCESS;
    }
}
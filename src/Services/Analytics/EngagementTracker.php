<?php

namespace LumeSocial\Services\Analytics;

use LumeSocial\Models\Post;
use LumeSocial\Models\EngagementMetric;
use LumeSocial\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EngagementTracker
{
    public function trackEngagement(Organization $organization, string $type, int $value = 1): void
    {
        EngagementMetric::create([
            'organization_id' => $organization->id,
            'metric_type' => $type,
            'value' => $value,
            'date' => Carbon::today(),
        ]);
    }

    public function getMetricsForOrganization(Organization $organization, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $query = EngagementMetric::query()
            ->where('organization_id', $organization->id);

        if ($start) {
            $query->where('date', '>=', $start);
        }

        if ($end) {
            $query->where('date', '<=', $end);
        }

        $metrics = $query->get()
            ->groupBy('metric_type')
            ->map(function ($group) {
                return $group->sum('value');
            });

        return $metrics->toArray();
    }

    public function getDailyMetrics(Organization $organization, string $type, int $days = 30): array
    {
        return EngagementMetric::query()
            ->where('organization_id', $organization->id)
            ->where('metric_type', $type)
            ->where('date', '>=', Carbon::today()->subDays($days))
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($metric) => $metric->date->format('Y-m-d'))
            ->map(fn ($group) => $group->sum('value'))
            ->toArray();
    }

    public function getMetrics(Post $post, ?string $type = null): Collection
    {
        $query = EngagementMetric::query()
            ->where('post_id', $post->id);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('recorded_at', 'desc')->get();
    }

    public function getAggregatedMetrics(Post $post): array
    {
        return EngagementMetric::query()
            ->where('post_id', $post->id)
            ->selectRaw('type, SUM(value) as total, AVG(value) as average')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($metric) {
                return [$metric->type => [
                    'total' => (float) $metric->total,
                    'average' => (float) $metric->average,
                ]];
            })
            ->toArray();
    }

    public function getOrganizationMetrics(int $organizationId, ?Carbon $startDate = null): Collection
    {
        $query = EngagementMetric::query()
            ->where('organization_id', $organizationId);

        if ($startDate) {
            $query->where('recorded_at', '>=', $startDate);
        }

        return $query->orderBy('recorded_at', 'desc')->get();
    }

    public function deleteMetrics(Post $post): bool
    {
        return EngagementMetric::query()
            ->where('post_id', $post->id)
            ->delete();
    }

    public function cleanupOldMetrics(int $days = 90): int
    {
        $cutoff = Carbon::now()->subDays($days);

        return EngagementMetric::query()
            ->where('recorded_at', '<', $cutoff)
            ->delete();
    }
} 
<?php

namespace App\Services;

use App\Enums\WebhookEventStatus;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array{
     *     projects: int,
     *     webhookEndpoints: int,
     *     eventsLast24h: int,
     *     failedEventsLast24h: int,
     * }
     */
    public function statsForUser(User $user): array
    {
        $projectIds = $user->projects()->pluck('id');

        $recentEvents = DB::table('webhook_events')
            ->whereIn('project_id', $projectIds)
            ->where('created_at', '>=', now()->subDay());

        return [
            'projects' => $projectIds->count(),
            'webhookEndpoints' => DB::table('webhook_endpoints')->whereIn('project_id', $projectIds)->count(),
            'eventsLast24h' => (clone $recentEvents)->count(),
            'failedEventsLast24h' => (clone $recentEvents)->where('status', WebhookEventStatus::Failed->value)->count(),
        ];
    }

    /**
     * @return array<int, array{id: string, status: string, received_at: string, webhookEndpoint: array{id: string, name: string}}>
     */
    public function recentEventsForUser(User $user, int $limit = 10): array
    {
        $projectIds = $user->projects()->pluck('id');

        return WebhookEvent::query()
            ->whereIn('project_id', $projectIds)
            ->with('webhookEndpoint:id,name')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (WebhookEvent $event) => [
                'id' => $event->id,
                'status' => $event->status->value,
                'received_at' => $event->received_at,
                'webhookEndpoint' => [
                    'id' => $event->webhookEndpoint->id,
                    'name' => $event->webhookEndpoint->name,
                ],
            ])
            ->all();
    }
}

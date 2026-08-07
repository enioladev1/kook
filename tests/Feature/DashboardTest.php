<?php

namespace Tests\Feature;

use App\Enums\WebhookEventStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_stats_only_reflect_the_current_users_own_data()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $project = Project::factory()->for($user)->create();
        $endpoint = WebhookEndpoint::factory()->for($project)->create();
        WebhookEvent::factory()->create([
            'webhook_endpoint_id' => $endpoint->id,
            'project_id' => $project->id,
            'status' => WebhookEventStatus::Failed,
        ]);

        $otherProject = Project::factory()->for($otherUser)->create();
        $otherEndpoint = WebhookEndpoint::factory()->for($otherProject)->create();
        WebhookEvent::factory()->count(3)->create([
            'webhook_endpoint_id' => $otherEndpoint->id,
            'project_id' => $otherProject->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('stats.projects', 1)
            ->where('stats.webhookEndpoints', 1)
            ->where('stats.eventsLast24h', 1)
            ->where('stats.failedEventsLast24h', 1)
            ->has('recentEvents', 1)
        );
    }
}

<?php

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;

test('guests cannot view audit logs', function () {
    $this->get('/audit-logs')->assertRedirect('/login');
});

test('a user sees audit logs for their own projects', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    AuditLog::create(['user_id' => $user->id, 'project_id' => $project->id, 'action' => 'project.created']);

    $this->actingAs($user)
        ->get('/audit-logs')
        ->assertInertia(fn ($page) => $page
            ->component('audit-logs/index')
            ->has('logs.data', 1)
        );
});

test('a user does not see audit logs for another users project', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    AuditLog::create(['user_id' => $owner->id, 'project_id' => $project->id, 'action' => 'project.created']);

    $this->actingAs($viewer)
        ->get('/audit-logs')
        ->assertInertia(fn ($page) => $page
            ->component('audit-logs/index')
            ->has('logs.data', 0)
        );
});

test('a user sees their own account-level audit logs with no project', function () {
    $user = User::factory()->create();
    AuditLog::create(['user_id' => $user->id, 'project_id' => null, 'action' => 'auth.login']);

    $this->actingAs($user)
        ->get('/audit-logs')
        ->assertInertia(fn ($page) => $page
            ->component('audit-logs/index')
            ->has('logs.data', 1)
        );
});

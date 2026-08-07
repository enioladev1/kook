<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    /**
     * @return Collection<int, Project>
     */
    public function forUser(User $user): Collection
    {
        /** @var Collection<int, Project> */
        return $user->projects()
            ->withCount(['webhookEndpoints', 'apiKeys'])
            ->latest()
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Project
    {
        /** @var Project */
        return $user->projects()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function slugExistsForUser(User $user, string $slug): bool
    {
        return $user->projects()->where('slug', $slug)->exists();
    }
}

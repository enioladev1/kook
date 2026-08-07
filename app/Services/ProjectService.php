<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Repositories\ProjectRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @return Collection<int, Project>
     */
    public function listForUser(User $user): Collection
    {
        return $this->projects->forUser($user);
    }

    public function create(User $user, string $name): Project
    {
        return DB::transaction(function () use ($user, $name) {
            $project = $this->projects->create($user, [
                'name' => $name,
                'slug' => $this->uniqueSlug($user, $name),
            ]);

            $this->auditLog->record($user, $project, 'project.created', $project);

            return $project;
        });
    }

    public function update(User $user, Project $project, string $name): Project
    {
        return DB::transaction(function () use ($user, $project, $name) {
            $project = $this->projects->update($project, ['name' => $name]);

            $this->auditLog->record($user, $project, 'project.updated', $project);

            return $project;
        });
    }

    public function delete(User $user, Project $project): void
    {
        DB::transaction(function () use ($user, $project) {
            $this->auditLog->record($user, null, 'project.deleted', null, [
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]);

            $this->projects->delete($project);
        });
    }

    private function uniqueSlug(User $user, string $name): string
    {
        $base = Str::slug($name) ?: 'project';
        $slug = $base;
        $suffix = 1;

        while ($this->projects->slugExistsForUser($user, $slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

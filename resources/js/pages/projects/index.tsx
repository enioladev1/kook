import {
    ArrowRight02Icon,
    Folder02Icon,
    PlusSignIcon,
} from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form, Head, Link } from '@inertiajs/react';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import { PageHeader } from '@/components/dashboard/page-header';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { show } from '@/routes/projects';
import type { Project } from '@/types';

export default function ProjectsIndex({ projects }: { projects: Project[] }) {
    return (
        <>
            <Head title="Projects" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <PageHeader
                    eyebrow={`${projects.length} ${projects.length === 1 ? 'project' : 'projects'}`}
                    title="Projects"
                    description="Group your webhook endpoints by application or environment."
                >
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button
                                className="bg-signal text-signal-foreground hover:bg-signal/90"
                                data-test="new-project-button"
                            >
                                <HugeiconsIcon
                                    icon={PlusSignIcon}
                                    className="size-4"
                                />
                                New project
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Create project</DialogTitle>
                            <DialogDescription>
                                Projects group related webhook endpoints,
                                events, and API keys.
                            </DialogDescription>

                            <Form
                                {...ProjectController.store.form()}
                                resetOnSuccess
                                className="space-y-6"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                required
                                                autoFocus
                                                placeholder="Production"
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="secondary">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                disabled={processing}
                                                className="bg-signal text-signal-foreground hover:bg-signal/90"
                                                data-test="create-project-button"
                                            >
                                                Create project
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </PageHeader>

                {projects.length === 0 ? (
                    <div className="flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-border p-16 text-center">
                        <HugeiconsIcon
                            icon={Folder02Icon}
                            className="size-8 text-muted-foreground"
                        />
                        <p className="font-medium">No projects yet</p>
                        <p className="max-w-sm text-sm text-muted-foreground">
                            Create a project to start receiving and forwarding
                            webhooks.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {projects.map((project) => (
                            <Link
                                key={project.id}
                                href={show(project)}
                                data-test={`project-link-${project.slug}`}
                                className="group rounded-2xl border border-border bg-card p-5 transition-colors hover:border-signal/40"
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex size-9 items-center justify-center rounded-xl bg-signal/10">
                                        <HugeiconsIcon
                                            icon={Folder02Icon}
                                            className="size-4.5 text-signal"
                                        />
                                    </div>
                                    <HugeiconsIcon
                                        icon={ArrowRight02Icon}
                                        className="size-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                    />
                                </div>
                                <p className="mt-4 font-semibold">
                                    {project.name}
                                </p>
                                <p className="mt-0.5 font-mono text-xs text-muted-foreground">
                                    {project.slug}
                                </p>
                                <p className="mt-3 text-sm text-muted-foreground">
                                    {project.webhook_endpoints_count}{' '}
                                    {project.webhook_endpoints_count === 1
                                        ? 'endpoint'
                                        : 'endpoints'}{' '}
                                    · {project.api_keys_count} API{' '}
                                    {project.api_keys_count === 1
                                        ? 'key'
                                        : 'keys'}
                                </p>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

ProjectsIndex.layout = {
    breadcrumbs: [{ title: 'Projects', href: '/projects' }],
};

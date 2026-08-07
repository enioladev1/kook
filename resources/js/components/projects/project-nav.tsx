import { ChevronDownIcon, Settings02Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import { index as projectsIndex, show as showProject } from '@/routes/projects';
import type { Project } from '@/types';

export type ProjectNavTab = 'endpoints' | 'events' | 'api-keys' | 'settings';

function tabHref(project: Project, tab: ProjectNavTab) {
    return showProject(project, { query: { tab } });
}

function NavTab({
    project,
    tab,
    active,
    children,
}: {
    project: Project;
    tab: ProjectNavTab;
    active: boolean;
    children: ReactNode;
}) {
    return (
        <Link
            href={tabHref(project, tab)}
            className={cn(
                'inline-flex items-center border-b-2 py-4 text-sm font-medium transition-colors',
                active
                    ? 'border-signal text-foreground'
                    : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
            )}
            data-test={`project-nav-${tab}`}
        >
            {children}
        </Link>
    );
}

export function ProjectNav({
    project,
    projects,
    active,
}: {
    project: Project;
    projects: Project[];
    active: ProjectNavTab;
}) {
    const otherProjects = projects.filter((p) => p.id !== project.id);

    return (
        <div className="border-b border-border bg-card px-4 md:px-6">
            <div className="flex items-center gap-2">
                <div className="flex min-w-0 items-center gap-4 overflow-x-auto">
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            className="flex shrink-0 items-center gap-1.5 py-4 text-sm font-semibold outline-none"
                            data-test="project-switcher"
                        >
                            {project.name}
                            <HugeiconsIcon
                                icon={ChevronDownIcon}
                                className="size-4 text-muted-foreground"
                            />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start">
                            <DropdownMenuLabel>
                                Switch project
                            </DropdownMenuLabel>
                            {otherProjects.length === 0 ? (
                                <p className="px-2 py-1.5 text-sm text-muted-foreground">
                                    No other projects
                                </p>
                            ) : (
                                otherProjects.map((p) => (
                                    <DropdownMenuItem key={p.id} asChild>
                                        <Link href={showProject(p)}>
                                            {p.name}
                                        </Link>
                                    </DropdownMenuItem>
                                ))
                            )}
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link href={projectsIndex()}>All projects</Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <div className="h-6 w-px shrink-0 bg-border" />

                    <nav className="flex shrink-0 items-center gap-6">
                        <NavTab
                            project={project}
                            tab="endpoints"
                            active={active === 'endpoints'}
                        >
                            Endpoints
                        </NavTab>
                        <NavTab
                            project={project}
                            tab="events"
                            active={active === 'events'}
                        >
                            Events
                        </NavTab>
                        <NavTab
                            project={project}
                            tab="api-keys"
                            active={active === 'api-keys'}
                        >
                            API keys
                        </NavTab>
                    </nav>
                </div>

                <Link
                    href={tabHref(project, 'settings')}
                    className={cn(
                        'ml-auto flex shrink-0 items-center gap-1.5 text-sm transition-colors',
                        active === 'settings'
                            ? 'text-foreground'
                            : 'text-muted-foreground hover:text-foreground',
                    )}
                    data-test="project-nav-settings"
                >
                    <HugeiconsIcon icon={Settings02Icon} className="size-4" />
                    <span className="hidden sm:inline">Project settings</span>
                </Link>
            </div>
        </div>
    );
}

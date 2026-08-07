import {
    Activity01Icon,
    AlertCircleIcon,
    Folder02Icon,
    PlugSocketIcon,
} from '@hugeicons/core-free-icons';
import { Head, Link } from '@inertiajs/react';
import { PageHeader } from '@/components/dashboard/page-header';
import { StatCard } from '@/components/dashboard/stat-card';
import { StatusChip } from '@/components/dashboard/status-chip';
import { eventStatusTone } from '@/lib/status-tones';
import { dashboard } from '@/routes';
import { index as projectsIndex } from '@/routes/projects';
import { show as showEvent } from '@/routes/webhook-events';
import type { WebhookEventStatus } from '@/types';

type DashboardStats = {
    projects: number;
    webhookEndpoints: number;
    eventsLast24h: number;
    failedEventsLast24h: number;
};

type RecentEvent = {
    id: string;
    status: WebhookEventStatus;
    received_at: string;
    webhookEndpoint: { id: string; name: string };
};

export default function Dashboard({
    stats,
    recentEvents,
}: {
    stats: DashboardStats;
    recentEvents: RecentEvent[];
}) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <PageHeader
                    eyebrow="Overview"
                    title="Dashboard"
                    description="A snapshot of every project you're running through Kook."
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        icon={Folder02Icon}
                        label="Projects"
                        value={stats.projects}
                    />
                    <StatCard
                        icon={PlugSocketIcon}
                        label="Webhook endpoints"
                        value={stats.webhookEndpoints}
                    />
                    <StatCard
                        icon={Activity01Icon}
                        label="Events, last 24h"
                        value={stats.eventsLast24h}
                    />
                    <StatCard
                        icon={AlertCircleIcon}
                        label="Failed, last 24h"
                        value={stats.failedEventsLast24h}
                    />
                </div>

                <div className="rounded-2xl border border-border bg-card">
                    <div className="border-b border-border px-6 py-4">
                        <h2 className="font-semibold">Recent events</h2>
                    </div>

                    {recentEvents.length === 0 ? (
                        <div className="flex flex-col items-center justify-center gap-2 px-6 py-16 text-center">
                            <p className="text-sm text-muted-foreground">
                                No webhook events yet.
                            </p>
                            <Link
                                href={projectsIndex()}
                                className="text-sm font-medium text-signal hover:underline"
                            >
                                Create a project to get started
                            </Link>
                        </div>
                    ) : (
                        <div className="divide-y divide-border">
                            {recentEvents.map((event) => (
                                <Link
                                    key={event.id}
                                    href={showEvent(event)}
                                    className="flex flex-wrap items-center justify-between gap-3 px-6 py-4 transition-colors hover:bg-accent/50"
                                    data-test={`recent-event-${event.id}`}
                                >
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {event.webhookEndpoint.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {new Date(
                                                event.received_at,
                                            ).toLocaleString()}
                                        </p>
                                    </div>
                                    <StatusChip
                                        tone={eventStatusTone[event.status]}
                                    >
                                        {event.status}
                                    </StatusChip>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};

import { ArrowLeft01Icon, Refresh01Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import WebhookEndpointController from '@/actions/App/Http/Controllers/WebhookEndpointController';
import { CopyField } from '@/components/copy-field';
import { PageHeader } from '@/components/dashboard/page-header';
import { StatusChip } from '@/components/dashboard/status-chip';
import InputError from '@/components/input-error';
import { Pagination } from '@/components/pagination';
import { EmptyState } from '@/components/projects/empty-state';
import { ProjectNav } from '@/components/projects/project-nav';
import { ProviderLogo } from '@/components/providers/provider-logo';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { endpointHealth } from '@/lib/endpoint-health';
import { providerSecretGuidance } from '@/lib/provider-secret-guidance';
import { endpointStatusTone, eventStatusTone } from '@/lib/status-tones';
import { show as showProject } from '@/routes/projects';
import { show as showEvent } from '@/routes/webhook-events';
import type {
    Paginated,
    Project,
    WebhookEndpoint,
    WebhookEvent,
} from '@/types';

function Section({
    title,
    children,
    className = '',
}: {
    title: string;
    children: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={`rounded-2xl border border-border bg-card ${className}`}
        >
            <div className="border-b border-border px-6 py-4">
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="space-y-4 p-6">{children}</div>
        </div>
    );
}

export default function WebhookEndpointsShow({
    project,
    projects,
    webhookEndpoint,
    events,
}: {
    project: Project;
    projects: Project[];
    webhookEndpoint: WebhookEndpoint;
    events: Paginated<WebhookEvent>;
}) {
    const [ingestUrl] = useState(
        () =>
            `${window.location.origin}/webhooks/${webhookEndpoint.ingest_token}`,
    );
    const [regenerateOpen, setRegenerateOpen] = useState(false);
    const health = endpointHealth(webhookEndpoint);
    const secretGuidance = providerSecretGuidance(
        webhookEndpoint.provider?.key,
    );

    return (
        <>
            <Head title={webhookEndpoint.name} />

            <ProjectNav
                project={project}
                projects={projects}
                active="endpoints"
            />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Link
                    href={showProject(project, {
                        query: { tab: 'endpoints' },
                    })}
                    className="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                    data-test="back-to-endpoints-link"
                >
                    <HugeiconsIcon icon={ArrowLeft01Icon} className="size-4" />
                    Back to endpoints
                </Link>

                <PageHeader
                    eyebrow={project.name}
                    title={
                        <span className="flex flex-wrap items-center gap-3">
                            {webhookEndpoint.provider && (
                                <ProviderLogo
                                    provider={webhookEndpoint.provider}
                                    className="h-7"
                                    imageClassName="h-4"
                                />
                            )}
                            {webhookEndpoint.name}
                            <StatusChip
                                tone={
                                    endpointStatusTone[webhookEndpoint.status]
                                }
                            >
                                {webhookEndpoint.status}
                            </StatusChip>
                            {health && (
                                <StatusChip tone={health.tone}>
                                    last delivery: {health.label}
                                </StatusChip>
                            )}
                        </span>
                    }
                />

                <div className="grid gap-6 md:grid-cols-2">
                    <Section title="Ingest URL">
                        <CopyField value={ingestUrl} />
                        <p className="text-sm text-muted-foreground">
                            {webhookEndpoint.mode === 'managed'
                                ? `Configure this URL in ${webhookEndpoint.provider?.name ?? 'your provider'} and verify signatures with the secret you set below.`
                                : 'Configure this URL with your provider. The original payload and headers are forwarded as-is.'}
                        </p>
                    </Section>

                    <Section title="Endpoint settings">
                        <Form
                            {...WebhookEndpointController.update.form(
                                webhookEndpoint,
                            )}
                            options={{ preserveScroll: true }}
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
                                            defaultValue={webhookEndpoint.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="destination_url">
                                            Destination URL
                                        </Label>
                                        <Input
                                            id="destination_url"
                                            name="destination_url"
                                            required
                                            defaultValue={
                                                webhookEndpoint.destination_url
                                            }
                                        />
                                        <InputError
                                            message={errors.destination_url}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select
                                            name="status"
                                            defaultValue={
                                                webhookEndpoint.status
                                            }
                                        >
                                            <SelectTrigger
                                                id="status"
                                                className="w-full"
                                            >
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">
                                                    Active
                                                </SelectItem>
                                                <SelectItem value="paused">
                                                    Paused
                                                </SelectItem>
                                                <SelectItem value="disabled">
                                                    Disabled
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.status} />
                                    </div>

                                    {webhookEndpoint.mode === 'managed' && (
                                        <div className="grid gap-2">
                                            <Label htmlFor="provider_secret">
                                                Provider webhook secret
                                            </Label>
                                            <Input
                                                id="provider_secret"
                                                name="provider_secret"
                                                type="password"
                                                autoComplete="off"
                                                placeholder="Leave blank to keep the current secret"
                                            />
                                            {secretGuidance && (
                                                <p className="text-sm text-muted-foreground">
                                                    {secretGuidance}
                                                </p>
                                            )}
                                            <InputError
                                                message={errors.provider_secret}
                                            />
                                        </div>
                                    )}

                                    <Button
                                        disabled={processing}
                                        className="bg-signal text-signal-foreground hover:bg-signal/90"
                                        data-test="update-webhook-endpoint-button"
                                    >
                                        Save
                                    </Button>
                                </>
                            )}
                        </Form>
                    </Section>
                </div>

                {webhookEndpoint.mode === 'managed' && (
                    <Section title="Signing secret">
                        <CopyField value={webhookEndpoint.signing_secret} />
                        <p className="text-sm text-muted-foreground">
                            Kook signs every forwarded request with this secret
                            via the{' '}
                            <code className="rounded bg-muted px-1 py-0.5">
                                X-Kook-Signature
                            </code>{' '}
                            header. Verify it on your server before trusting a
                            request.
                        </p>

                        <Dialog
                            open={regenerateOpen}
                            onOpenChange={setRegenerateOpen}
                        >
                            <DialogTrigger asChild>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    data-test="regenerate-signing-secret-button"
                                >
                                    <HugeiconsIcon
                                        icon={Refresh01Icon}
                                        className="size-4"
                                    />
                                    Regenerate
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogTitle>
                                    Regenerate signing secret
                                </DialogTitle>
                                <DialogDescription>
                                    The current secret will stop working
                                    immediately. Update it on your receiving
                                    server before regenerating, or deliveries
                                    will fail signature checks until you do.
                                </DialogDescription>

                                <Form
                                    {...WebhookEndpointController.regenerateSigningSecret.form(
                                        webhookEndpoint,
                                    )}
                                    options={{ preserveScroll: true }}
                                    onSuccess={() => setRegenerateOpen(false)}
                                >
                                    {({ processing }) => (
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="secondary">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                variant="destructive"
                                                disabled={processing}
                                                data-test="confirm-regenerate-signing-secret-button"
                                            >
                                                Regenerate
                                            </Button>
                                        </DialogFooter>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </Section>
                )}

                <div className="rounded-2xl border border-border bg-card">
                    <div className="border-b border-border px-6 py-4">
                        <h2 className="font-semibold">Events</h2>
                        <p className="text-sm text-muted-foreground">
                            {events.total} received in total
                        </p>
                    </div>

                    {events.data.length === 0 ? (
                        <EmptyState
                            title="No events yet"
                            description="Once this endpoint receives a webhook, it will show up here."
                        />
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[720px] text-sm">
                                <thead>
                                    <tr className="border-b border-border text-left text-xs text-muted-foreground uppercase">
                                        <th className="px-6 py-3 font-medium">
                                            Received
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Event
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Status
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Signature
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Idempotency key
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {events.data.map((event) => (
                                        <Link
                                            key={event.id}
                                            as="tr"
                                            href={showEvent(event)}
                                            className="cursor-pointer transition-colors hover:bg-accent/50"
                                            data-test={`event-link-${event.id}`}
                                        >
                                            <td className="px-6 py-3 whitespace-nowrap">
                                                {new Date(
                                                    event.received_at,
                                                ).toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3 font-mono text-xs">
                                                {event.event_name ?? 'n/a'}
                                            </td>
                                            <td className="px-4 py-3">
                                                <StatusChip
                                                    tone={
                                                        eventStatusTone[
                                                            event.status
                                                        ]
                                                    }
                                                >
                                                    {event.status}
                                                </StatusChip>
                                            </td>
                                            <td className="px-4 py-3 text-muted-foreground">
                                                {event.signature_valid === null
                                                    ? 'n/a'
                                                    : event.signature_valid
                                                      ? 'valid'
                                                      : 'invalid'}
                                            </td>
                                            <td className="px-6 py-3 font-mono text-xs text-muted-foreground">
                                                {event.idempotency_key ?? 'n/a'}
                                            </td>
                                        </Link>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    <div className="px-6 py-4">
                        <Pagination paginator={events} />
                    </div>
                </div>
            </div>
        </>
    );
}

import { ArrowLeft01Icon, RefreshIcon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form, Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageHeader } from '@/components/dashboard/page-header';
import { StatusChip } from '@/components/dashboard/status-chip';
import { Button } from '@/components/ui/button';
import { deliveryStatusTone, eventStatusTone } from '@/lib/status-tones';
import { show as showEndpoint } from '@/routes/webhook-endpoints';
import { replay } from '@/routes/webhook-events';
import type { WebhookDelivery, WebhookEventStatus } from '@/types';

function Section({ title, children }: { title: string; children: ReactNode }) {
    return (
        <div className="rounded-2xl border border-border bg-card">
            <div className="border-b border-border px-6 py-4">
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="p-6">{children}</div>
        </div>
    );
}

type EventDetail = {
    id: string;
    event_name: string | null;
    status: WebhookEventStatus;
    signature_valid: boolean | null;
    received_at: string;
    payload: Record<string, unknown>;
    headers: Record<string, string>;
    webhookEndpoint: { id: string; name: string };
};

export default function EventsShow({
    event,
    deliveries,
}: {
    event: EventDetail;
    deliveries: WebhookDelivery[];
}) {
    return (
        <>
            <Head title={`Event ${event.id}`} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Link
                    href={showEndpoint(event.webhookEndpoint)}
                    className="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                    data-test="back-to-endpoint-link"
                >
                    <HugeiconsIcon icon={ArrowLeft01Icon} className="size-4" />
                    Back to {event.webhookEndpoint.name}
                </Link>

                <PageHeader
                    eyebrow={event.webhookEndpoint.name}
                    title={
                        <span className="flex flex-wrap items-center gap-3">
                            {event.event_name ?? 'Event'}
                            <StatusChip tone={eventStatusTone[event.status]}>
                                {event.status}
                            </StatusChip>
                            {event.signature_valid !== null && (
                                <StatusChip
                                    tone={
                                        event.signature_valid
                                            ? 'success'
                                            : 'danger'
                                    }
                                >
                                    {event.signature_valid
                                        ? 'valid signature'
                                        : 'invalid signature'}
                                </StatusChip>
                            )}
                        </span>
                    }
                    description={new Date(event.received_at).toLocaleString()}
                >
                    {event.status === 'success' && (
                        <Form
                            {...replay.form(event)}
                            options={{ preserveScroll: true }}
                        >
                            {({ processing }) => (
                                <Button
                                    type="submit"
                                    variant="secondary"
                                    disabled={processing}
                                    data-test="replay-event-button"
                                >
                                    <HugeiconsIcon
                                        icon={RefreshIcon}
                                        className="size-4"
                                    />
                                    Replay
                                </Button>
                            )}
                        </Form>
                    )}
                </PageHeader>

                <Section title="Payload">
                    <pre className="max-h-96 overflow-auto rounded-xl bg-muted p-4 font-mono text-xs whitespace-pre-wrap">
                        {JSON.stringify(event.payload, null, 2)}
                    </pre>
                </Section>

                <Section title="Headers">
                    <dl className="grid gap-2 font-mono text-xs">
                        {Object.entries(event.headers).map(([name, value]) => (
                            <div
                                key={name}
                                className="grid grid-cols-1 gap-1 sm:grid-cols-3 sm:gap-2"
                            >
                                <dt className="truncate text-muted-foreground">
                                    {name}
                                </dt>
                                <dd className="break-words sm:col-span-2 sm:truncate">
                                    {value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Section>

                <Section title="Delivery attempts">
                    {deliveries.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No delivery attempts yet.
                        </p>
                    ) : (
                        <div className="-m-6 divide-y divide-border">
                            {deliveries.map((delivery) => (
                                <div
                                    key={delivery.id}
                                    className="flex items-start justify-between gap-3 px-6 py-4"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-medium">
                                            Attempt {delivery.attempt_number}
                                        </p>
                                        <p className="text-sm break-words text-muted-foreground">
                                            {delivery.http_status_code
                                                ? `HTTP ${delivery.http_status_code}`
                                                : (delivery.error_message ??
                                                  'No response')}
                                            {delivery.duration_ms !== null &&
                                                ` in ${delivery.duration_ms}ms`}
                                        </p>
                                    </div>
                                    <StatusChip
                                        tone={
                                            deliveryStatusTone[delivery.status]
                                        }
                                        className="shrink-0"
                                    >
                                        {delivery.status}
                                    </StatusChip>
                                </div>
                            ))}
                        </div>
                    )}
                </Section>
            </div>
        </>
    );
}

import type { StatusTone } from '@/components/dashboard/status-chip';
import { deliveryStatusTone, eventStatusTone } from '@/lib/status-tones';
import type { WebhookEndpoint } from '@/types';

export function endpointHealth(
    endpoint: WebhookEndpoint,
): { label: string; tone: StatusTone } | null {
    const event = endpoint.latest_event;

    if (!event) {
        return null;
    }

    const delivery = event.latest_delivery;

    if (delivery) {
        return {
            label: delivery.status,
            tone: deliveryStatusTone[delivery.status],
        };
    }

    return { label: event.status, tone: eventStatusTone[event.status] };
}

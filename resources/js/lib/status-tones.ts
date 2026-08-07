import type { StatusTone } from '@/components/dashboard/status-chip';
import type {
    WebhookDeliveryStatus,
    WebhookEndpointStatus,
    WebhookEventStatus,
} from '@/types';

export const endpointStatusTone: Record<WebhookEndpointStatus, StatusTone> = {
    active: 'success',
    paused: 'warning',
    disabled: 'neutral',
};

export const eventStatusTone: Record<WebhookEventStatus, StatusTone> = {
    pending: 'neutral',
    processing: 'warning',
    success: 'success',
    failed: 'danger',
};

export const deliveryStatusTone: Record<WebhookDeliveryStatus, StatusTone> = {
    pending: 'neutral',
    retrying: 'warning',
    delivered: 'success',
    failed: 'danger',
};

import type {
    WebhookDeliveryStatus,
    WebhookEventStatus,
} from '@/types/webhook-event';

export type Provider = {
    id: string;
    key: string;
    name: string;
    docs_url: string | null;
    is_active: boolean;
};

export type WebhookEndpointMode = 'relay' | 'managed';
export type WebhookEndpointStatus = 'active' | 'paused' | 'disabled';

export type WebhookEndpoint = {
    id: string;
    project_id: string;
    name: string;
    mode: WebhookEndpointMode;
    destination_url: string;
    provider_id: string | null;
    provider: Provider | null;
    ingest_token: string;
    signing_secret: string;
    status: WebhookEndpointStatus;
    latest_event: {
        id: string;
        status: WebhookEventStatus;
        latest_delivery: {
            id: string;
            status: WebhookDeliveryStatus;
            attempt_number: number;
        } | null;
    } | null;
    created_at: string;
    updated_at: string;
};

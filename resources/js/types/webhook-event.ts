export type WebhookEventStatus =
    'pending' | 'processing' | 'success' | 'failed';
export type WebhookDeliveryStatus =
    'pending' | 'delivered' | 'failed' | 'retrying';

export type WebhookEvent = {
    id: string;
    webhook_endpoint_id: string;
    project_id: string;
    idempotency_key: string | null;
    event_name: string | null;
    headers: Record<string, string>;
    payload: Record<string, unknown>;
    raw_body: string;
    signature_valid: boolean | null;
    status: WebhookEventStatus;
    received_at: string;
    created_at: string;
};

export type WebhookDelivery = {
    id: string;
    event_id: string;
    attempt_number: number;
    status: WebhookDeliveryStatus;
    http_status_code: number | null;
    response_body: string | null;
    error_message: string | null;
    duration_ms: number | null;
    next_retry_at: string | null;
    delivered_at: string | null;
    created_at: string;
};

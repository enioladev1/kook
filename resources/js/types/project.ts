export type Project = {
    id: string;
    user_id: string;
    name: string;
    slug: string;
    failure_emails_enabled: boolean;
    webhook_endpoints_count: number;
    api_keys_count: number;
    created_at: string;
    updated_at: string;
};

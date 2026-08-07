export type ApiKey = {
    id: string;
    project_id: string;
    name: string;
    key_prefix: string;
    last_used_at: string | null;
    expires_at: string | null;
    revoked_at: string | null;
    created_at: string;
};

export type AuditLog = {
    id: string;
    user_id: string | null;
    project_id: string | null;
    action: string;
    auditable_type: string | null;
    auditable_id: string | null;
    ip_address: string | null;
    metadata: Record<string, unknown> | null;
    created_at: string;
    user: {
        id: string;
        name: string;
        email: string;
    } | null;
};

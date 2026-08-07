export type EmailProvider = 'resend' | 'postmark' | 'sendbyte' | 'smtp';
export type SmtpEncryption = 'tls' | 'ssl' | 'none';

export type EmailSetting = {
    id: string;
    provider: EmailProvider;
    from_address: string | null;
    from_name: string | null;
    smtp_host: string | null;
    smtp_port: number | null;
    smtp_username: string | null;
    smtp_encryption: SmtpEncryption | null;
    has_api_key: boolean;
    has_smtp_password: boolean;
    created_at: string;
    updated_at: string;
};

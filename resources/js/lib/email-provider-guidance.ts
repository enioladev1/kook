import type { EmailProvider } from '@/types';

const GUIDANCE: Record<Exclude<EmailProvider, 'smtp'>, string> = {
    resend: 'Find this in the Resend dashboard under API Keys.',
    postmark:
        'Find this in your Postmark server under API Tokens (Server API token).',
    sendbyte:
        'Find this in the SendByte dashboard under API keys. Starts with sk_test_ (sandbox) or sk_live_ (production).',
};

export function emailApiKeyGuidance(provider: EmailProvider): string | null {
    if (provider === 'smtp') {
        return null;
    }

    return GUIDANCE[provider];
}

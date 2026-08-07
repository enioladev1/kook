const GUIDANCE: Record<string, string> = {
    stripe: 'Stripe generates this automatically when you register the endpoint in your Stripe Dashboard: Developers → Webhooks → your endpoint → Signing secret (starts with whsec_).',
    github: "You choose this value yourself when creating the webhook in your repository or organization's Settings → Webhooks. Enter the same value there.",
    shopify:
        "Your app's Client secret, found in the Shopify Partner Dashboard under your app's API credentials.",
    paystack:
        'Your Paystack Secret Key from Settings → API Keys & Webhooks. This is the same key you use to authenticate normal API requests.',
    flutterwave:
        'Your Secret Hash from the Flutterwave Dashboard under Settings → Webhooks. You choose this value yourself.',
    generic_hmac:
        'A shared secret only you and the sender know. The sender must sign the raw request body with HMAC-SHA256 and send the hex digest in an X-Webhook-Signature header.',
};

export function providerSecretGuidance(
    providerKey: string | undefined,
): string | null {
    if (!providerKey) {
        return null;
    }

    return GUIDANCE[providerKey] ?? null;
}

import {
    ApiIcon,
    FileSecurityIcon,
    PlugSocketIcon,
    RefreshIcon,
    RepeatIcon,
    Route01Icon,
    ServerStack01Icon,
    Shield01Icon,
    Timer01Icon,
} from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { SectionHeading } from '@/components/marketing/section-heading';

const features = [
    {
        icon: Route01Icon,
        title: 'Transparent relay',
        description:
            "Forwards the exact payload and headers you received, byte for byte, when you don't need managed verification.",
    },
    {
        icon: Shield01Icon,
        title: 'Managed verification',
        description:
            "Checks the signature against the provider's secret before your app ever sees the request.",
    },
    {
        icon: Timer01Icon,
        title: 'Automatic retries',
        description:
            'Retries failed deliveries on a five-step backoff schedule, with the full attempt history kept.',
    },
    {
        icon: RepeatIcon,
        title: 'Idempotent ingestion',
        description:
            "Duplicate deliveries of the same event, matched by the provider's event ID, are recognized and never reprocessed.",
    },
    {
        icon: RefreshIcon,
        title: 'Replay on demand',
        description:
            'Resend any successfully verified event from the dashboard whenever you need to reprocess it.',
    },
    {
        icon: FileSecurityIcon,
        title: 'Append-only audit log',
        description:
            'Records every action. Nothing gets edited or deleted after the fact.',
    },
    {
        icon: PlugSocketIcon,
        title: 'Provider library',
        description:
            'Stripe, GitHub, Shopify, Paystack, and Flutterwave signature schemes, ready to use, plus a configurable generic HMAC option.',
    },
    {
        icon: ApiIcon,
        title: 'API access',
        description:
            'A small JSON API authenticated by key, for listing endpoints and events and triggering replays programmatically.',
    },
    {
        icon: ServerStack01Icon,
        title: 'Self-hosted',
        description:
            'Runs on your own infrastructure. Your webhook data never leaves it.',
    },
];

export function FeatureGrid() {
    return (
        <section id="features" className="mx-auto w-full max-w-6xl px-6 py-24">
            <SectionHeading
                eyebrow="Capabilities"
                title="Everything a webhook has to survive on the way in"
            />

            <div className="mt-14 grid gap-px overflow-hidden rounded-xl border border-[#1B1F26] bg-[#1B1F26] sm:grid-cols-2 lg:grid-cols-3">
                {features.map((feature) => (
                    <div key={feature.title} className="bg-[#0E1116] p-8">
                        <HugeiconsIcon
                            icon={feature.icon}
                            className="size-5 text-[#FF7A33]"
                        />
                        <h3 className="mt-4 text-base font-bold">
                            {feature.title}
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-[#8A93A6]">
                            {feature.description}
                        </p>
                    </div>
                ))}
            </div>
        </section>
    );
}

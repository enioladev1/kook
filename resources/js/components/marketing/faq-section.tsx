import { SectionHeading } from '@/components/marketing/section-heading';

const faqs = [
    {
        question: 'Is Kook multi-tenant?',
        answer: 'No. Kook is built for a single admin account. The first person to register becomes that account, and registration closes automatically after that, there is no ongoing sign-up.',
    },
    {
        question: 'What does it run on?',
        answer: 'PHP 8.4, PostgreSQL 16, and Redis. A Docker Compose file is included for local development, and a separate production Dockerfile and Compose file for deployment.',
    },
    {
        question:
            'What happens if my application is down when a webhook arrives?',
        answer: 'Kook keeps the event and retries the delivery on a backoff schedule of 30 seconds, 2 minutes, 10 minutes, 30 minutes, then 1 hour. You can also replay it manually at any time after that.',
    },
    {
        question: 'Does my webhook data ever leave my own infrastructure?',
        answer: 'No. There is no hosted version of Kook and no third party in the request path. Everything runs on servers you control.',
    },
    {
        question: "Can I verify a provider that isn't listed?",
        answer: 'Yes. A configurable generic HMAC provider covers custom or unlisted providers, using your own header name and hashing algorithm.',
    },
    {
        question:
            'How is this different from just pointing the provider directly at my app?',
        answer: 'Your app stays reachable at one stable URL while Kook absorbs retries, signature checks, and duplicate deliveries in front of it, and every event is kept even if your app was briefly down when it arrived.',
    },
];

export function FaqSection() {
    return (
        <section id="faq" className="mx-auto w-full max-w-6xl px-6 py-24">
            <SectionHeading eyebrow="Questions" title="Before you self-host" />

            <div className="mt-14 grid gap-x-12 gap-y-10 sm:grid-cols-2">
                {faqs.map((faq) => (
                    <div key={faq.question}>
                        <h3 className="text-base font-bold">{faq.question}</h3>
                        <p className="mt-2 text-sm leading-relaxed text-[#8A93A6]">
                            {faq.answer}
                        </p>
                    </div>
                ))}
            </div>
        </section>
    );
}

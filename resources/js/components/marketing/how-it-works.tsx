import { SectionHeading } from '@/components/marketing/section-heading';

const steps = [
    {
        number: '01',
        title: 'Receive',
        description:
            'Your provider sends a request to a unique ingest URL for each endpoint you create. Kook stores the raw payload and headers immediately and responds within milliseconds, so the provider never times out waiting on your application.',
    },
    {
        number: '02',
        title: 'Verify',
        description:
            "If you've turned on managed verification, Kook checks the request's signature against the provider's secret using that provider's own algorithm, before anything is forwarded to you.",
    },
    {
        number: '03',
        title: 'Deliver',
        description:
            'The verified event is forwarded to your application. If your endpoint is unreachable or returns an error, Kook retries on a backoff schedule of 30 seconds, 2 minutes, 10 minutes, 30 minutes, then 1 hour.',
    },
    {
        number: '04',
        title: 'Audit',
        description:
            'Every action across your projects, from an endpoint being created to a key being revoked, is written to a log that cannot be edited or deleted once it exists.',
    },
];

export function HowItWorks() {
    return (
        <section
            id="how-it-works"
            className="mx-auto w-full max-w-6xl px-6 py-24"
        >
            <SectionHeading
                eyebrow="Pipeline"
                title="What happens to every request"
                description="Four stages, in this order, every time an event arrives."
            />

            <ol className="mt-14 grid list-none gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                {steps.map((step, index) => (
                    <li key={step.number} className="relative">
                        <div className="flex items-center gap-3">
                            <span className="font-mono text-sm text-[#FF7A33]">
                                {step.number}
                            </span>
                            <span className="h-px flex-1 bg-[#242A33]" />
                        </div>
                        <h3 className="mt-4 text-lg font-bold">{step.title}</h3>
                        <p className="mt-2 text-sm leading-relaxed text-[#8A93A6]">
                            {step.description}
                        </p>
                        {index < steps.length - 1 && (
                            <span
                                aria-hidden="true"
                                className="absolute top-[7px] -right-4 hidden font-mono text-sm text-[#4A5261] lg:block"
                            >
                                →
                            </span>
                        )}
                    </li>
                ))}
            </ol>
        </section>
    );
}

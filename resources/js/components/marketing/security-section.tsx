import {
    Activity01Icon,
    DatabaseLockedIcon,
    GlobalIcon,
    LockKeyIcon,
    Router01Icon,
    Shield02Icon,
} from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { SectionHeading } from '@/components/marketing/section-heading';

const measures = [
    {
        icon: Shield02Icon,
        title: 'Ownership checked on every request',
        description:
            "A request for a project, endpoint, or event that isn't yours returns a plain 404, never a 403 that would confirm it exists at all.",
    },
    {
        icon: LockKeyIcon,
        title: 'Secrets encrypted at rest',
        description:
            "Provider secrets and Kook's own outgoing signing secret are encrypted. API keys store only a hash, the plaintext is shown once at creation and never stored again.",
    },
    {
        icon: GlobalIcon,
        title: 'Outbound requests kept internal',
        description:
            'Destination URLs are validated to reject private and reserved network addresses, so delivery cannot be pointed back at your own infrastructure.',
    },
    {
        icon: Activity01Icon,
        title: 'Rate limiting throughout',
        description:
            'Applied to authentication, webhook ingestion per endpoint, replay actions, and the API per key.',
    },
    {
        icon: DatabaseLockedIcon,
        title: 'Append-only, enforced twice',
        description:
            'A database trigger rejects updates and deletes to the audit log outright, and the application refuses before a query is even attempted.',
    },
    {
        icon: Router01Icon,
        title: 'No wildcard origins',
        description:
            'You configure exactly which origins may make cross-origin requests. There is no default that allows all of them.',
    },
];

export function SecuritySection() {
    return (
        <section id="security" className="mx-auto w-full max-w-6xl px-6 py-24">
            <SectionHeading
                eyebrow="Security"
                title="Built for handling other people's secrets"
                description="Kook sits between your provider and your application, so it carries the same responsibility either side would. These are the defaults, not options you have to turn on."
            />

            <div className="mt-14 grid gap-x-8 gap-y-10 sm:grid-cols-2">
                {measures.map((measure) => (
                    <div key={measure.title} className="flex gap-4">
                        <HugeiconsIcon
                            icon={measure.icon}
                            className="mt-1 size-5 shrink-0 text-[#FF7A33]"
                        />
                        <div>
                            <h3 className="text-base font-bold">
                                {measure.title}
                            </h3>
                            <p className="mt-1.5 text-sm leading-relaxed text-[#8A93A6]">
                                {measure.description}
                            </p>
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}

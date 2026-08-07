import { SectionHeading } from '@/components/marketing/section-heading';

const commands = [
    'git clone <your-repository-url>',
    'cd kook',
    'cp .env.example .env',
    'docker compose up -d',
    'php artisan migrate --seed',
];

const notes = [
    'Runs on Laravel, PHP 8.4, PostgreSQL 16, and Redis. A production Dockerfile and Compose file are included alongside the local development one.',
    'Built for a single admin account. Registration is a one-time setup step and closes automatically once that account exists, there is no multi-tenant sign-up.',
    'The application, worker, and web server are stateless, sessions, cache, and queues all live in Redis, so any of them can run as multiple replicas behind a load balancer.',
];

export function SelfHostingSection() {
    return (
        <section
            id="self-hosting"
            className="mx-auto w-full max-w-6xl px-6 py-24"
        >
            <SectionHeading
                eyebrow="Deployment"
                title="On infrastructure you already trust"
            />

            <div className="mt-14 grid gap-12 lg:grid-cols-2 lg:items-start">
                <div className="space-y-6">
                    {notes.map((note) => (
                        <p
                            key={note}
                            className="text-base leading-relaxed text-[#8A93A6]"
                        >
                            {note}
                        </p>
                    ))}
                </div>

                <div className="rounded-xl border border-[#242A33] bg-[#12151A]">
                    <div className="flex items-center gap-2 border-b border-[#242A33] px-4 py-3">
                        <span className="size-2 rounded-full bg-[#8A93A6]/40" />
                        <span className="size-2 rounded-full bg-[#8A93A6]/40" />
                        <span className="size-2 rounded-full bg-[#8A93A6]/40" />
                        <span className="ml-2 font-mono text-xs text-[#8A93A6]">
                            setup.sh
                        </span>
                    </div>
                    <div className="space-y-2.5 px-5 py-6 font-mono text-sm">
                        {commands.map((command) => (
                            <div key={command} className="flex gap-3">
                                <span className="text-[#8A93A6] select-none">
                                    $
                                </span>
                                <span className="text-[#F3F1EA]">
                                    {command}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

import { GithubIcon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Link } from '@inertiajs/react';
import { PulseTrace } from '@/components/pulse-trace';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

const REPO_URL = 'https://github.com/enioladev1/kook';

export default function AuthSplitLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="dark flex min-h-svh font-sans text-[#F3F1EA]">
            <div className="relative hidden w-full max-w-md shrink-0 flex-col justify-between overflow-hidden bg-[#101216] p-10 lg:flex">
                <PulseTrace className="pointer-events-none absolute inset-x-0 top-0 h-24 w-full opacity-30" />

                <Link href={home()} className="relative flex items-center">
                    <img
                        src="/branding/logo.png"
                        alt="Kook"
                        className="h-7 w-auto"
                    />
                </Link>

                <div className="relative flex items-center justify-between gap-4">
                    <p className="max-w-[220px] text-sm text-[#8A93A6]">
                        Self-hosted webhook infrastructure you run and own.
                    </p>
                    <a
                        href={REPO_URL}
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="View Kook on GitHub"
                        className="flex size-9 shrink-0 items-center justify-center rounded-full border border-[#242A33] text-[#8A93A6] transition outline-none hover:border-[#FF7A33]/40 hover:text-[#F3F1EA] focus-visible:ring-2 focus-visible:ring-[#FF7A33] focus-visible:ring-offset-2 focus-visible:ring-offset-[#101216]"
                    >
                        <HugeiconsIcon icon={GithubIcon} className="size-4.5" />
                    </a>
                </div>
            </div>

            <div className="flex w-full flex-1 flex-col items-center justify-center bg-[#0B0D10] p-6 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link
                                href={home()}
                                className="rounded-md outline-none focus-visible:ring-2 focus-visible:ring-[#FF7A33] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0D10] lg:hidden"
                            >
                                <img
                                    src="/branding/logo.png"
                                    alt="Kook"
                                    className="h-8 w-auto"
                                />
                            </Link>

                            <div className="space-y-2 text-center">
                                <h1 className="text-xl font-extrabold tracking-tight">
                                    {title}
                                </h1>
                                <p className="text-center text-sm text-[#8A93A6]">
                                    {description}
                                </p>
                            </div>
                        </div>

                        <div className="rounded-xl border border-[#242A33] bg-[#12151A] p-8">
                            {children}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

import { Link } from '@inertiajs/react';
import { PulseTrace } from '@/components/marketing/pulse-trace';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="dark relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-[#0B0D10] p-6 font-sans text-[#F3F1EA] md:p-10">
            <PulseTrace className="pointer-events-none absolute inset-x-0 top-0 h-20 w-full opacity-30" />

            <div className="relative w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={home()}
                            className="flex flex-col items-center gap-3 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-[#FF7A33] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0D10]"
                        >
                            <img
                                src="/branding/logo.png"
                                alt="Kook"
                                className="h-10 w-auto"
                            />
                            <span className="sr-only">{title}</span>
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
    );
}

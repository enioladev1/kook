import { Copy01Icon, Tick01Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Head, Link, usePage } from '@inertiajs/react';
import { FaqSection } from '@/components/marketing/faq-section';
import { FeatureGrid } from '@/components/marketing/feature-grid';
import { HowItWorks } from '@/components/marketing/how-it-works';
import { PulseTrace } from '@/components/marketing/pulse-trace';
import { PulseUnderline } from '@/components/marketing/pulse-underline';
import { SecuritySection } from '@/components/marketing/security-section';
import { SelfHostingSection } from '@/components/marketing/self-hosting-section';
import { TerminalPanel } from '@/components/marketing/terminal-panel';
import { useClipboard } from '@/hooks/use-clipboard';
import { dashboard, login, register } from '@/routes';

const providers = ['Stripe', 'GitHub', 'Shopify', 'Paystack', 'Flutterwave'];

const navLinks = [
    { href: '#how-it-works', label: 'How it works' },
    { href: '#features', label: 'Features' },
    { href: '#security', label: 'Security' },
    { href: '#self-hosting', label: 'Deployment' },
];

const linkFocus =
    'outline-none focus-visible:ring-2 focus-visible:ring-[#FF7A33] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B0D10] rounded-md';

export default function Welcome() {
    const { auth } = usePage().props;
    const [copiedText, copy] = useClipboard();
    const installCommand = 'docker compose up -d';

    return (
        <>
            <Head title="Kook - Self-hosted webhook infrastructure" />

            <div className="flex min-h-screen flex-col bg-[#0B0D10] font-sans text-[#F3F1EA] selection:bg-[#FF7A33]/30">
                <header className="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
                    <div className="flex items-center gap-2.5">
                        <img
                            src="/branding/logo.png"
                            alt="Kook"
                            className="h-8 w-auto"
                        />
                    </div>

                    <nav className="hidden items-center gap-8 lg:flex">
                        {navLinks.map((link) => (
                            <a
                                key={link.href}
                                href={link.href}
                                className={`text-sm font-medium text-[#8A93A6] transition hover:text-[#F3F1EA] ${linkFocus}`}
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>

                    <div className="flex items-center gap-1">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className={`rounded-md bg-[#FF7A33] px-4 py-2 text-sm font-semibold text-[#0B0D10] transition hover:bg-[#FF9052] ${linkFocus}`}
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className={`px-3 py-2 text-sm font-medium text-[#8A93A6] transition hover:text-[#F3F1EA] ${linkFocus}`}
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className={`ml-2 rounded-md bg-[#FF7A33] px-4 py-2 text-sm font-semibold text-[#0B0D10] transition hover:bg-[#FF9052] ${linkFocus}`}
                                >
                                    Get started
                                </Link>
                            </>
                        )}
                    </div>
                </header>

                <main className="flex-1">
                    <section className="relative mx-auto flex w-full max-w-5xl flex-col items-center px-6 pt-16 pb-24 text-center sm:pt-24">
                        <PulseTrace className="pointer-events-none absolute inset-x-0 top-4 h-24 w-full opacity-60 sm:top-10" />

                        <div className="relative flex items-center gap-2 rounded-full border border-[#242A33] bg-[#12151A] px-3 py-1.5">
                            <span className="size-1.5 rounded-full bg-[#FF7A33]" />
                            <span className="font-mono text-[11px] tracking-[0.18em] text-[#8A93A6] uppercase">
                                Self-hosted webhook infrastructure
                            </span>
                        </div>

                        <h1 className="relative mt-8 text-5xl leading-[0.98] font-extrabold tracking-tight text-balance sm:text-6xl lg:text-7xl">
                            Signal in.{' '}
                            <span className="relative inline-block">
                                Verified.
                                <PulseUnderline className="absolute -bottom-1 left-0 h-3 w-full sm:-bottom-2" />
                            </span>{' '}
                            Delivered.
                        </h1>

                        <p className="relative mt-8 max-w-2xl text-lg text-balance text-[#8A93A6]">
                            Kook receives your webhooks, confirms they're
                            genuinely from Stripe, GitHub, Shopify, and more,
                            retries what fails, and keeps a permanent record of
                            it all. It runs entirely on infrastructure you
                            control.
                        </p>

                        <div className="relative mt-10 flex flex-col items-center gap-5">
                            <Link
                                href={auth.user ? dashboard() : register()}
                                className={`rounded-md bg-[#FF7A33] px-7 py-3 text-base font-semibold text-[#0B0D10] transition hover:bg-[#FF9052] ${linkFocus}`}
                            >
                                {auth.user ? 'Go to dashboard' : 'Get started'}
                            </Link>

                            <button
                                type="button"
                                onClick={() => copy(installCommand)}
                                className={`flex items-center gap-2 font-mono text-xs text-[#8A93A6] transition hover:text-[#F3F1EA] ${linkFocus}`}
                            >
                                <span>$ {installCommand}</span>
                                <HugeiconsIcon
                                    icon={
                                        copiedText === installCommand
                                            ? Tick01Icon
                                            : Copy01Icon
                                    }
                                    className="size-3.5"
                                />
                            </button>
                        </div>
                    </section>

                    <section className="mx-auto w-full max-w-3xl px-6 pb-24">
                        <TerminalPanel />
                    </section>

                    <section className="mx-auto w-full max-w-5xl border-y border-[#1B1F26] px-6 py-8">
                        <p className="text-center font-mono text-[11px] tracking-[0.18em] text-[#5C6472] uppercase">
                            Verifies signatures from
                        </p>
                        <div className="mt-5 flex flex-wrap items-center justify-center gap-x-10 gap-y-3">
                            {providers.map((provider) => (
                                <span
                                    key={provider}
                                    className="font-mono text-sm text-[#8A93A6]"
                                >
                                    {provider}
                                </span>
                            ))}
                        </div>
                    </section>

                    <HowItWorks />

                    <div className="mx-auto w-full max-w-6xl border-t border-[#1B1F26] px-6" />

                    <FeatureGrid />

                    <div className="mx-auto w-full max-w-6xl border-t border-[#1B1F26] px-6" />

                    <SecuritySection />

                    <div className="mx-auto w-full max-w-6xl border-t border-[#1B1F26] px-6" />

                    <SelfHostingSection />

                    <div className="mx-auto w-full max-w-6xl border-t border-[#1B1F26] px-6" />

                    <FaqSection />
                </main>

                <footer className="border-t border-[#1B1F26] px-6 py-10">
                    <div className="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-6 text-center sm:flex-row sm:text-left">
                        <div className="flex items-center gap-3">
                            <img
                                src="/branding/logo.png"
                                alt="Kook"
                                className="h-6 w-auto"
                            />
                            <p className="font-mono text-xs text-[#5C6472]">
                                Open source and self-hosted, MIT licensed.
                            </p>
                        </div>

                        <nav className="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                            {navLinks.map((link) => (
                                <a
                                    key={link.href}
                                    href={link.href}
                                    className={`text-sm text-[#8A93A6] transition hover:text-[#F3F1EA] ${linkFocus}`}
                                >
                                    {link.label}
                                </a>
                            ))}
                        </nav>
                    </div>
                </footer>
            </div>
        </>
    );
}

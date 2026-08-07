import { ArrowRight02Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/dashboard/page-header';
import { ProviderLogo } from '@/components/providers/provider-logo';
import type { Provider } from '@/types';

export default function ProvidersIndex({
    providers,
}: {
    providers: Provider[];
}) {
    return (
        <>
            <Head title="Providers" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <PageHeader
                    eyebrow={`${providers.length} supported`}
                    title="Providers"
                    description="Providers supported by managed verification. Select one when creating a webhook endpoint."
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {providers.map((provider) => (
                        <div
                            key={provider.id}
                            className="rounded-2xl border border-border bg-card p-5"
                        >
                            <ProviderLogo
                                provider={provider}
                                className="h-10"
                                imageClassName="h-6"
                            />
                            <p className="mt-4 font-semibold">
                                {provider.name}
                            </p>
                            {provider.docs_url && (
                                <a
                                    href={provider.docs_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-2 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-signal"
                                >
                                    View documentation
                                    <HugeiconsIcon
                                        icon={ArrowRight02Icon}
                                        className="size-3.5"
                                    />
                                </a>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}

ProvidersIndex.layout = {
    breadcrumbs: [{ title: 'Providers', href: '/providers' }],
};

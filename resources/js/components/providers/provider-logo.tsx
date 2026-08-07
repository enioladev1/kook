import { PlugSocketIcon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

// Logos are a mix of formats (svg where provided, png otherwise), so try
// each extension in turn and fall back to a generic icon if none exist.
const EXTENSIONS = ['svg', 'png'] as const;

export function ProviderLogo({
    provider,
    className,
    imageClassName = 'h-5',
}: {
    provider: { key: string; name: string };
    className?: string;
    imageClassName?: string;
}) {
    const [attempt, setAttempt] = useState(0);
    const extension = EXTENSIONS[attempt];

    return (
        <div
            className={cn(
                'inline-flex shrink-0 items-center justify-center rounded-lg bg-white px-2 py-1.5',
                className,
            )}
        >
            {extension ? (
                <img
                    src={`/provider-logos/${provider.key}.${extension}`}
                    alt={`${provider.name} logo`}
                    className={cn('w-auto object-contain', imageClassName)}
                    onError={() => setAttempt((a) => a + 1)}
                />
            ) : (
                <HugeiconsIcon
                    icon={PlugSocketIcon}
                    className="size-4.5 text-neutral-500"
                />
            )}
        </div>
    );
}

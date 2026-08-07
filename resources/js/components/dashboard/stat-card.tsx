import { HugeiconsIcon } from '@hugeicons/react';
import type { IconSvgElement } from '@hugeicons/react';

export function StatCard({
    icon,
    label,
    value,
    caption,
}: {
    icon: IconSvgElement;
    label: string;
    value: number | string;
    caption?: string;
}) {
    return (
        <div className="rounded-2xl border border-border bg-card p-5">
            <div className="flex items-center gap-2.5">
                <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-signal/10">
                    <HugeiconsIcon
                        icon={icon}
                        className="size-4.5 text-signal"
                    />
                </div>
                <p className="text-sm text-muted-foreground">{label}</p>
            </div>
            <p className="mt-4 text-3xl font-bold tracking-tight tabular-nums">
                {value}
            </p>
            {caption && (
                <p className="mt-1 text-xs text-muted-foreground">{caption}</p>
            )}
        </div>
    );
}

import { cn } from '@/lib/utils';

export type StatusTone = 'success' | 'warning' | 'danger' | 'neutral';

const toneClasses: Record<StatusTone, string> = {
    success:
        'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30 [&_span]:bg-emerald-500',
    warning:
        'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30 [&_span]:bg-amber-500',
    danger: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30 [&_span]:bg-red-500',
    neutral:
        'bg-muted text-muted-foreground ring-border [&_span]:bg-muted-foreground/50',
};

export function StatusChip({
    tone,
    children,
    className,
}: {
    tone: StatusTone;
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <span
            className={cn(
                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
                toneClasses[tone],
                className,
            )}
        >
            <span className="size-1.5 shrink-0 rounded-full" />
            {children}
        </span>
    );
}

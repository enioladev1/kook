import type { ReactNode } from 'react';
import { DormantTrace } from '@/components/projects/dormant-trace';

export function EmptyState({
    title,
    description,
    action,
}: {
    title: string;
    description: string;
    action?: ReactNode;
}) {
    return (
        <div className="flex flex-col items-center gap-6 px-6 py-16 text-center">
            <DormantTrace className="h-10 w-60" />
            <div className="space-y-1.5">
                <p className="font-semibold">{title}</p>
                <p className="max-w-sm text-sm text-muted-foreground">
                    {description}
                </p>
            </div>
            {action}
        </div>
    );
}

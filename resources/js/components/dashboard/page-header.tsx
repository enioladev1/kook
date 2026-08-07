import type { ReactNode } from 'react';

export function PageHeader({
    eyebrow,
    title,
    description,
    children,
}: {
    eyebrow?: string;
    title: ReactNode;
    description?: string;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-start justify-between gap-4">
            <div>
                {eyebrow && (
                    <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
                        {eyebrow}
                    </p>
                )}
                <h1 className="mt-1 text-2xl font-bold tracking-tight">
                    {title}
                </h1>
                {description && (
                    <p className="mt-1 text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            {children && (
                <div className="flex items-center gap-2">{children}</div>
            )}
        </div>
    );
}

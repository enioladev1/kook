import { Head } from '@inertiajs/react';
import { PageHeader } from '@/components/dashboard/page-header';
import { Pagination } from '@/components/pagination';
import type { AuditLog, Paginated } from '@/types';

export default function AuditLogsIndex({
    logs,
}: {
    logs: Paginated<AuditLog>;
}) {
    return (
        <>
            <Head title="Audit logs" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <PageHeader
                    eyebrow={`${logs.total} entries`}
                    title="Audit logs"
                    description="An append-only record of security-relevant actions across your projects."
                />

                <div className="rounded-2xl border border-border bg-card">
                    {logs.data.length === 0 ? (
                        <p className="p-6 text-sm text-muted-foreground">
                            No activity recorded yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[640px] text-sm">
                                <thead>
                                    <tr className="border-b border-border text-left text-xs text-muted-foreground uppercase">
                                        <th className="px-6 py-3 font-medium">
                                            When
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Action
                                        </th>
                                        <th className="px-4 py-3 font-medium">
                                            Actor
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            IP address
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {logs.data.map((log) => (
                                        <tr
                                            key={log.id}
                                            className="transition-colors hover:bg-accent/50"
                                        >
                                            <td className="px-6 py-3 whitespace-nowrap text-muted-foreground">
                                                {new Date(
                                                    log.created_at,
                                                ).toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3 font-mono text-xs">
                                                {log.action}
                                            </td>
                                            <td className="px-4 py-3">
                                                {log.user
                                                    ? log.user.name
                                                    : 'System'}
                                            </td>
                                            <td className="px-6 py-3 text-muted-foreground">
                                                {log.ip_address ?? 'n/a'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    <div className="px-6 py-4">
                        <Pagination paginator={logs} />
                    </div>
                </div>
            </div>
        </>
    );
}

AuditLogsIndex.layout = {
    breadcrumbs: [{ title: 'Audit logs', href: '/audit-logs' }],
};

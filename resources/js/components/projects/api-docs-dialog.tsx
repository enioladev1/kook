import { BookOpen01Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { CopyField } from '@/components/copy-field';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const ENDPOINTS: Array<{
    method: 'GET' | 'POST';
    path: string;
    description: string;
}> = [
    {
        method: 'GET',
        path: '/webhook-endpoints',
        description: "List this project's webhook endpoints.",
    },
    {
        method: 'GET',
        path: '/webhook-endpoints/{id}/events',
        description: 'List events received on an endpoint.',
    },
    {
        method: 'GET',
        path: '/events/{id}',
        description: "Fetch a single event's details.",
    },
    {
        method: 'POST',
        path: '/events/{id}/replay',
        description: 'Replay an event. Requires an Idempotency-Key header.',
    },
];

export function ApiDocsDialog() {
    const baseUrl = `${window.location.origin}/api/v1`;

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="ghost" size="sm">
                    <HugeiconsIcon icon={BookOpen01Icon} className="size-4" />
                    API documentation
                </Button>
            </DialogTrigger>
            <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
                <DialogTitle>API documentation</DialogTitle>
                <DialogDescription>
                    Use an API key to call these endpoints programmatically.
                </DialogDescription>

                <div className="space-y-6">
                    <div className="space-y-2">
                        <p className="text-sm font-medium">Base URL</p>
                        <CopyField value={baseUrl} />
                    </div>

                    <div className="space-y-2">
                        <p className="text-sm font-medium">Authentication</p>
                        <p className="text-sm text-muted-foreground">
                            Send the API key as a bearer token on every request.
                        </p>
                        <pre className="overflow-x-auto rounded-md bg-muted p-3 text-sm">
                            Authorization: Bearer &lt;your-api-key&gt;
                        </pre>
                    </div>

                    <div className="space-y-2">
                        <p className="text-sm font-medium">Endpoints</p>
                        <div className="divide-y divide-border rounded-md border">
                            {ENDPOINTS.map((endpoint) => (
                                <div
                                    key={`${endpoint.method}-${endpoint.path}`}
                                    className="flex items-start gap-3 p-3"
                                >
                                    <Badge
                                        variant={
                                            endpoint.method === 'GET'
                                                ? 'secondary'
                                                : 'default'
                                        }
                                        className="mt-0.5 shrink-0"
                                    >
                                        {endpoint.method}
                                    </Badge>
                                    <div className="min-w-0">
                                        <p className="truncate font-mono text-sm">
                                            {endpoint.path}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {endpoint.description}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <p className="text-sm font-medium">Example</p>
                        <pre className="overflow-x-auto rounded-md bg-muted p-3 text-sm">
                            {`curl ${baseUrl}/webhook-endpoints \\
  -H "Authorization: Bearer <your-api-key>"`}
                        </pre>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}

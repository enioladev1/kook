import { Delete02Icon, PlusSignIcon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form } from '@inertiajs/react';
import { useState } from 'react';
import ApiKeyController from '@/actions/App/Http/Controllers/ApiKeyController';
import { CopyField } from '@/components/copy-field';
import { StatusChip } from '@/components/dashboard/status-chip';
import InputError from '@/components/input-error';
import { EmptyState } from '@/components/projects/empty-state';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ApiKey, Project } from '@/types';
import { ApiDocsDialog } from './api-docs-dialog';

function CreateApiKeyDialog({ project }: { project: Project }) {
    const [open, setOpen] = useState(false);
    const [createdKey, setCreatedKey] = useState<string | null>(null);

    function handleOpenChange(next: boolean) {
        setOpen(next);

        if (!next) {
            setCreatedKey(null);
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button variant="secondary" data-test="new-api-key-button">
                    <HugeiconsIcon icon={PlusSignIcon} className="size-4" />
                    New API key
                </Button>
            </DialogTrigger>
            <DialogContent>
                {createdKey ? (
                    <>
                        <DialogTitle>API key created</DialogTitle>
                        <DialogDescription>
                            Copy this key now. You will not be able to see it
                            again.
                        </DialogDescription>

                        <CopyField value={createdKey} />

                        <DialogFooter>
                            <Button
                                onClick={() => handleOpenChange(false)}
                                data-test="done-api-key-button"
                            >
                                Done
                            </Button>
                        </DialogFooter>
                    </>
                ) : (
                    <>
                        <DialogTitle>Create API key</DialogTitle>
                        <DialogDescription>
                            API keys grant programmatic access to this project's
                            webhook endpoints and events.
                        </DialogDescription>

                        <Form
                            {...ApiKeyController.store.form(project)}
                            resetOnSuccess
                            onSuccess={(page) =>
                                setCreatedKey(page.flash?.newApiKey ?? null)
                            }
                            className="space-y-6"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="api_key_name">
                                            Name
                                        </Label>
                                        <Input
                                            id="api_key_name"
                                            name="name"
                                            required
                                            autoFocus
                                            placeholder="CI pipeline"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button variant="secondary">
                                                Cancel
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            disabled={processing}
                                            className="bg-signal text-signal-foreground hover:bg-signal/90"
                                            data-test="create-api-key-button"
                                        >
                                            Create key
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

function DeleteApiKeyDialog({ apiKey }: { apiKey: ApiKey }) {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    data-test={`delete-api-key-${apiKey.id}`}
                >
                    <HugeiconsIcon icon={Delete02Icon} className="size-4" />
                    Delete
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete API key</DialogTitle>
                <DialogDescription>
                    This permanently deletes "{apiKey.name}". Anything using it
                    will immediately lose access. This cannot be undone.
                </DialogDescription>

                <Form
                    {...ApiKeyController.destroy.form(apiKey)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                disabled={processing}
                                data-test={`confirm-delete-api-key-${apiKey.id}`}
                            >
                                Delete key
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export function ApiKeysCard({
    project,
    apiKeys,
}: {
    project: Project;
    apiKeys: ApiKey[];
}) {
    return (
        <div className="rounded-2xl border border-border bg-card">
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-border px-6 py-4">
                <h2 className="font-semibold">API keys</h2>
                <div className="flex items-center gap-2">
                    <ApiDocsDialog />
                    <CreateApiKeyDialog project={project} />
                </div>
            </div>
            <div className={apiKeys.length === 0 ? '' : 'p-6'}>
                {apiKeys.length === 0 ? (
                    <EmptyState
                        title="No API keys yet"
                        description="Create one to access this project's webhook endpoints and events programmatically."
                    />
                ) : (
                    <div className="divide-y divide-border">
                        {apiKeys.map((apiKey) => (
                            <div
                                key={apiKey.id}
                                className="flex flex-wrap items-center justify-between gap-3 py-3"
                            >
                                <div className="min-w-0">
                                    <p className="truncate font-medium">
                                        {apiKey.name}
                                    </p>
                                    <p className="font-mono text-sm text-muted-foreground">
                                        {apiKey.key_prefix}...
                                    </p>
                                </div>
                                <div className="flex items-center gap-2">
                                    {apiKey.revoked_at ? (
                                        <StatusChip tone="neutral">
                                            revoked
                                        </StatusChip>
                                    ) : (
                                        <Form
                                            {...ApiKeyController.revoke.form(
                                                apiKey,
                                            )}
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    type="submit"
                                                    variant="destructive"
                                                    size="sm"
                                                    disabled={processing}
                                                    data-test={`revoke-api-key-${apiKey.id}`}
                                                >
                                                    Revoke
                                                </Button>
                                            )}
                                        </Form>
                                    )}
                                    <DeleteApiKeyDialog apiKey={apiKey} />
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

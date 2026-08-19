import { Delete02Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form } from '@inertiajs/react';
import { useState } from 'react';
import WebhookEndpointController from '@/actions/App/Http/Controllers/WebhookEndpointController';
import { CopyField } from '@/components/copy-field';
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
import type { WebhookEndpoint } from '@/types';

export function DeleteEndpointDialog({
    webhookEndpoint,
}: {
    webhookEndpoint: WebhookEndpoint;
}) {
    const [open, setOpen] = useState(false);
    const [confirmation, setConfirmation] = useState('');
    const isConfirmed = confirmation === webhookEndpoint.name;

    function handleOpenChange(next: boolean) {
        setOpen(next);

        if (!next) {
            setConfirmation('');
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button
                    variant="destructive"
                    data-test="delete-endpoint-button"
                >
                    <HugeiconsIcon icon={Delete02Icon} className="size-4" />
                    Delete endpoint
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Delete endpoint</DialogTitle>
                <DialogDescription>
                    This permanently deletes "{webhookEndpoint.name}" and all of
                    its events and delivery history. This cannot be undone.
                </DialogDescription>

                <div className="space-y-4">
                    <div className="grid gap-2">
                        <Label>Endpoint name</Label>
                        <CopyField value={webhookEndpoint.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="delete_confirmation">
                            Type the endpoint name to confirm
                        </Label>
                        <Input
                            id="delete_confirmation"
                            value={confirmation}
                            onChange={(e) => setConfirmation(e.target.value)}
                            autoComplete="off"
                            autoFocus
                            data-test="delete-endpoint-confirmation-input"
                        />
                    </div>
                </div>

                <Form
                    {...WebhookEndpointController.destroy.form(webhookEndpoint)}
                    options={{ preserveScroll: true }}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                disabled={processing || !isConfirmed}
                                data-test="confirm-delete-endpoint-button"
                            >
                                Delete endpoint
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

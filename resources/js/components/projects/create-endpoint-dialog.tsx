import { PlusSignIcon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Form } from '@inertiajs/react';
import { useState } from 'react';
import WebhookEndpointController from '@/actions/App/Http/Controllers/WebhookEndpointController';
import InputError from '@/components/input-error';
import { ProviderLogo } from '@/components/providers/provider-logo';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { providerSecretGuidance } from '@/lib/provider-secret-guidance';
import type { Project, Provider, WebhookEndpointMode } from '@/types';

export function CreateEndpointDialog({
    project,
    providers,
}: {
    project: Project;
    providers: Provider[];
}) {
    const [mode, setMode] = useState<WebhookEndpointMode>('relay');
    const [providerId, setProviderId] = useState<string | undefined>();
    const selectedProvider = providers.find((p) => p.id === providerId);
    const secretGuidance = providerSecretGuidance(selectedProvider?.key);

    return (
        <Dialog
            onOpenChange={() => {
                setMode('relay');
                setProviderId(undefined);
            }}
        >
            <DialogTrigger asChild>
                <Button
                    className="bg-signal text-signal-foreground hover:bg-signal/90"
                    data-test="new-webhook-endpoint-button"
                >
                    <HugeiconsIcon icon={PlusSignIcon} className="size-4" />
                    New endpoint
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>Create webhook endpoint</DialogTitle>
                <DialogDescription>
                    Choose transparent relay to forward webhooks as-is, or
                    managed verification to have signatures checked
                    automatically.
                </DialogDescription>

                <Form
                    {...WebhookEndpointController.store.form(project)}
                    resetOnSuccess
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    autoFocus
                                    placeholder="Stripe production"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="destination_url">
                                    Destination URL
                                </Label>
                                <Input
                                    id="destination_url"
                                    name="destination_url"
                                    required
                                    placeholder="https://your-app.com/webhooks"
                                />
                                <InputError message={errors.destination_url} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="mode">Mode</Label>
                                <Select
                                    name="mode"
                                    value={mode}
                                    onValueChange={(value) =>
                                        setMode(value as WebhookEndpointMode)
                                    }
                                >
                                    <SelectTrigger id="mode" className="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="relay">
                                            Transparent relay
                                        </SelectItem>
                                        <SelectItem value="managed">
                                            Managed verification
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.mode} />
                            </div>

                            {mode === 'managed' && (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="provider_id">
                                            Provider
                                        </Label>
                                        <Select
                                            name="provider_id"
                                            value={providerId}
                                            onValueChange={setProviderId}
                                        >
                                            <SelectTrigger
                                                id="provider_id"
                                                className="w-full"
                                            >
                                                <SelectValue placeholder="Select a provider" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {providers.map((provider) => (
                                                    <SelectItem
                                                        key={provider.id}
                                                        value={provider.id}
                                                    >
                                                        <span className="flex items-center gap-2">
                                                            <ProviderLogo
                                                                provider={
                                                                    provider
                                                                }
                                                                className="h-6"
                                                                imageClassName="h-3.5"
                                                            />
                                                            {provider.name}
                                                        </span>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={errors.provider_id}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="provider_secret">
                                            Provider webhook secret
                                        </Label>
                                        <Input
                                            id="provider_secret"
                                            name="provider_secret"
                                            type="password"
                                            autoComplete="off"
                                        />
                                        {secretGuidance && (
                                            <p className="text-sm text-muted-foreground">
                                                {secretGuidance}
                                            </p>
                                        )}
                                        <InputError
                                            message={errors.provider_secret}
                                        />
                                    </div>
                                </>
                            )}

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary">Cancel</Button>
                                </DialogClose>
                                <Button
                                    disabled={processing}
                                    className="bg-signal text-signal-foreground hover:bg-signal/90"
                                    data-test="create-webhook-endpoint-button"
                                >
                                    Create endpoint
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

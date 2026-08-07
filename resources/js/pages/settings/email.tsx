import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import EmailSettingController from '@/actions/App/Http/Controllers/Settings/EmailSettingController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { emailApiKeyGuidance } from '@/lib/email-provider-guidance';
import { edit } from '@/routes/email-settings';
import type { EmailProvider, EmailSetting, SmtpEncryption } from '@/types';

type FormData = {
    provider: EmailProvider;
    from_address: string;
    from_name: string;
    api_key: string;
    smtp_host: string;
    smtp_port: string;
    smtp_username: string;
    smtp_password: string;
    smtp_encryption: SmtpEncryption;
    to: string;
};

const PROVIDER_LABELS: Record<EmailProvider, string> = {
    resend: 'Resend',
    postmark: 'Postmark',
    sendbyte: 'SendByte',
    smtp: 'SMTP',
};

export default function EmailSettings({
    emailSetting,
}: {
    emailSetting: EmailSetting;
}) {
    const form = useForm<FormData>({
        provider: emailSetting.provider,
        from_address: emailSetting.from_address ?? '',
        from_name: emailSetting.from_name ?? '',
        api_key: '',
        smtp_host: emailSetting.smtp_host ?? '',
        smtp_port: emailSetting.smtp_port ? String(emailSetting.smtp_port) : '',
        smtp_username: emailSetting.smtp_username ?? '',
        smtp_password: '',
        smtp_encryption: emailSetting.smtp_encryption ?? 'tls',
        to: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        form.put(EmailSettingController.update.url(), {
            preserveScroll: true,
        });
    };

    const sendTest: FormEventHandler = (e) => {
        e.preventDefault();

        form.post(EmailSettingController.test.url(), {
            preserveScroll: true,
        });
    };

    const usesApiKey = form.data.provider !== 'smtp';
    const apiKeyGuidance = emailApiKeyGuidance(form.data.provider);

    return (
        <>
            <Head title="Email setup" />

            <h1 className="sr-only">Email setup</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Email setup"
                    description="Choose how Kook sends outgoing email"
                />

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="provider">Provider</Label>
                        <Select
                            value={form.data.provider}
                            onValueChange={(value) =>
                                form.setData('provider', value as EmailProvider)
                            }
                        >
                            <SelectTrigger id="provider" className="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {(
                                    Object.keys(
                                        PROVIDER_LABELS,
                                    ) as EmailProvider[]
                                ).map((provider) => (
                                    <SelectItem key={provider} value={provider}>
                                        {PROVIDER_LABELS[provider]}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.provider} />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="from_address">From address</Label>
                            <Input
                                id="from_address"
                                type="email"
                                required
                                placeholder="hello@yourapp.com"
                                value={form.data.from_address}
                                onChange={(e) =>
                                    form.setData('from_address', e.target.value)
                                }
                            />
                            <InputError message={form.errors.from_address} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="from_name">From name</Label>
                            <Input
                                id="from_name"
                                placeholder="Kook"
                                value={form.data.from_name}
                                onChange={(e) =>
                                    form.setData('from_name', e.target.value)
                                }
                            />
                            <InputError message={form.errors.from_name} />
                        </div>
                    </div>

                    {usesApiKey ? (
                        <div className="grid gap-2">
                            <Label htmlFor="api_key">API key</Label>
                            <Input
                                id="api_key"
                                type="password"
                                autoComplete="off"
                                placeholder={
                                    emailSetting.has_api_key
                                        ? 'Leave blank to keep the current key'
                                        : undefined
                                }
                                value={form.data.api_key}
                                onChange={(e) =>
                                    form.setData('api_key', e.target.value)
                                }
                            />
                            {apiKeyGuidance && (
                                <p className="text-sm text-muted-foreground">
                                    {apiKeyGuidance}
                                </p>
                            )}
                            <InputError message={form.errors.api_key} />
                        </div>
                    ) : (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="smtp_host">Host</Label>
                                    <Input
                                        id="smtp_host"
                                        placeholder="smtp.yourapp.com"
                                        value={form.data.smtp_host}
                                        onChange={(e) =>
                                            form.setData(
                                                'smtp_host',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.smtp_host}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="smtp_port">Port</Label>
                                    <Input
                                        id="smtp_port"
                                        type="number"
                                        placeholder="587"
                                        value={form.data.smtp_port}
                                        onChange={(e) =>
                                            form.setData(
                                                'smtp_port',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.smtp_port}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="smtp_username">
                                        Username
                                    </Label>
                                    <Input
                                        id="smtp_username"
                                        autoComplete="off"
                                        value={form.data.smtp_username}
                                        onChange={(e) =>
                                            form.setData(
                                                'smtp_username',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.smtp_username}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="smtp_password">
                                        Password
                                    </Label>
                                    <Input
                                        id="smtp_password"
                                        type="password"
                                        autoComplete="off"
                                        placeholder={
                                            emailSetting.has_smtp_password
                                                ? 'Leave blank to keep the current password'
                                                : undefined
                                        }
                                        value={form.data.smtp_password}
                                        onChange={(e) =>
                                            form.setData(
                                                'smtp_password',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.smtp_password}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="smtp_encryption">
                                    Encryption
                                </Label>
                                <Select
                                    value={form.data.smtp_encryption}
                                    onValueChange={(value) =>
                                        form.setData(
                                            'smtp_encryption',
                                            value as SmtpEncryption,
                                        )
                                    }
                                >
                                    <SelectTrigger
                                        id="smtp_encryption"
                                        className="w-full"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="tls">TLS</SelectItem>
                                        <SelectItem value="ssl">SSL</SelectItem>
                                        <SelectItem value="none">
                                            None
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    message={form.errors.smtp_encryption}
                                />
                            </div>
                        </>
                    )}

                    <div className="flex items-center gap-4">
                        <Button
                            disabled={form.processing}
                            className="bg-signal text-signal-foreground hover:bg-signal/90"
                            data-test="update-email-settings-button"
                        >
                            Save
                        </Button>
                    </div>
                </form>
            </div>

            <div className="space-y-4">
                <Heading
                    variant="small"
                    title="Send a test email"
                    description="Test the credentials above before saving them"
                />

                <form
                    onSubmit={sendTest}
                    className="flex flex-col gap-4 sm:flex-row sm:items-end"
                >
                    <div className="grid flex-1 gap-2">
                        <Label htmlFor="to" className="sr-only">
                            Send to
                        </Label>
                        <Input
                            id="to"
                            type="email"
                            required
                            placeholder="you@yourapp.com"
                            value={form.data.to}
                            onChange={(e) => form.setData('to', e.target.value)}
                        />
                        <InputError message={form.errors.to} />
                    </div>

                    <Button
                        type="submit"
                        variant="secondary"
                        disabled={form.processing}
                        data-test="send-test-email-button"
                    >
                        Send test email
                    </Button>
                </form>
            </div>
        </>
    );
}

EmailSettings.layout = {
    breadcrumbs: [
        {
            title: 'Email setup',
            href: edit(),
        },
    ],
};

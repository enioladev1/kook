import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

const messages: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Forbidden',
        description: "You don't have permission to access this page.",
    },
    404: {
        title: 'Page not found',
        description:
            "The page you're looking for doesn't exist or has been moved.",
    },
    419: {
        title: 'Session expired',
        description:
            'Your session expired. Please refresh the page and try again.',
    },
    429: {
        title: 'Too many requests',
        description:
            "You've made too many requests. Please wait a moment and try again.",
    },
    500: {
        title: 'Something went wrong',
        description:
            'Unable to complete this request right now. Please try again later.',
    },
    503: {
        title: 'Service unavailable',
        description: "We're undergoing maintenance. Please check back shortly.",
    },
};

export default function ErrorPage({ status }: { status: number }) {
    const { title, description } = messages[status] ?? messages[500];

    return (
        <>
            <Head title={title} />
            <div className="flex min-h-screen flex-col items-center justify-center gap-4 p-6 text-center">
                <p className="text-sm font-medium text-muted-foreground">
                    {status}
                </p>
                <h1 className="text-2xl font-semibold">{title}</h1>
                <p className="max-w-sm text-muted-foreground">{description}</p>
                <Button asChild>
                    <Link href={dashboard()}>Back to dashboard</Link>
                </Button>
            </div>
        </>
    );
}

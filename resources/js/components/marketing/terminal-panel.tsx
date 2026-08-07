type LogLine = {
    prefix: string;
    prefixClassName: string;
    text: string;
    meta?: string;
    metaClassName?: string;
};

const LINES: LogLine[] = [
    {
        prefix: 'POST',
        prefixClassName: 'text-[#8A93A6]',
        text: '/webhooks/wh_9f2a1c8b3d',
    },
    {
        prefix: '✓',
        prefixClassName: 'text-[#57D9A3]',
        text: 'signature verified',
        meta: 'stripe · charge.succeeded',
        metaClassName: 'text-[#8A93A6]',
    },
    {
        prefix: '→',
        prefixClassName: 'text-[#8A93A6]',
        text: '202 Accepted',
        meta: '14ms',
        metaClassName: 'text-[#8A93A6]',
    },
    {
        prefix: '⟳',
        prefixClassName: 'text-[#FF7A33]',
        text: 'delivered → api.yourapp.com/hooks',
        meta: '200 OK',
        metaClassName: 'text-[#57D9A3]',
    },
];

export function TerminalPanel() {
    return (
        <div
            className="rounded-xl border border-[#242A33] bg-[#12151A] shadow-[0_0_60px_-15px_rgba(255,122,51,0.25)]"
            role="img"
            aria-label="Example webhook event log: request received, signature verified against Stripe, accepted with a 202 response in 14 milliseconds, and delivered to the destination with a 200 response."
        >
            <div className="flex items-center gap-2 border-b border-[#242A33] px-4 py-3">
                <span className="size-2 rounded-full bg-[#8A93A6]/40" />
                <span className="size-2 rounded-full bg-[#8A93A6]/40" />
                <span className="size-2 rounded-full bg-[#8A93A6]/40" />
                <span className="ml-2 font-mono text-xs text-[#8A93A6]">
                    webhook.log
                </span>
            </div>

            <div className="space-y-3 px-5 py-6 font-mono text-sm">
                {LINES.map((line, index) => (
                    <div
                        key={line.text}
                        className="animate-rise-in flex items-baseline justify-between gap-4"
                        style={{ animationDelay: `${index * 150 + 200}ms` }}
                    >
                        <span className="flex items-baseline gap-3 truncate">
                            <span
                                className={`w-10 shrink-0 ${line.prefixClassName}`}
                            >
                                {line.prefix}
                            </span>
                            <span className="truncate text-[#F3F1EA]">
                                {line.text}
                            </span>
                        </span>
                        {line.meta && (
                            <span
                                className={`shrink-0 text-xs ${line.metaClassName}`}
                            >
                                {line.meta}
                            </span>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}

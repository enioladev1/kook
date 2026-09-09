import { Copy01Icon, Tick01Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { useClipboard } from '@/hooks/use-clipboard';
import { highlightJson } from '@/lib/json-highlight';

export function PayloadViewer({ raw }: { raw: string }) {
    const [copiedText, copy] = useClipboard();
    const isCopied = copiedText === raw;
    const segments = useMemo(() => highlightJson(raw), [raw]);

    return (
        <div className="relative">
            <Button
                type="button"
                variant="secondary"
                size="icon"
                onClick={() => copy(raw)}
                aria-label="Copy payload"
                className="absolute top-2 right-2 z-10"
                data-test="copy-payload-button"
            >
                <HugeiconsIcon
                    icon={isCopied ? Tick01Icon : Copy01Icon}
                    className="size-4"
                />
            </Button>
            <pre className="max-h-96 overflow-auto rounded-xl bg-muted p-4 pr-14 font-mono text-xs whitespace-pre-wrap">
                {segments === null
                    ? raw
                    : segments.map((segment, index) => (
                          <span key={index} className={segment.className}>
                              {segment.text}
                          </span>
                      ))}
            </pre>
        </div>
    );
}

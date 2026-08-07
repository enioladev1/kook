import { Copy01Icon, Tick01Icon } from '@hugeicons/core-free-icons';
import { HugeiconsIcon } from '@hugeicons/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useClipboard } from '@/hooks/use-clipboard';

export function CopyField({ value }: { value: string }) {
    const [copiedText, copy] = useClipboard();
    const isCopied = copiedText === value;

    return (
        <div className="flex items-center gap-2">
            <Input readOnly value={value} className="font-mono text-sm" />
            <Button
                type="button"
                variant="secondary"
                size="icon"
                onClick={() => copy(value)}
                aria-label="Copy to clipboard"
            >
                <HugeiconsIcon
                    icon={isCopied ? Tick01Icon : Copy01Icon}
                    className="size-4"
                />
            </Button>
        </div>
    );
}

import {
    ComputerIcon,
    Moon02Icon,
    Sun03Icon,
} from '@hugeicons/core-free-icons';
import type { IconSvgElement } from '@hugeicons/react';
import { HugeiconsIcon } from '@hugeicons/react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

const OPTIONS: { value: Appearance; icon: IconSvgElement; label: string }[] = [
    { value: 'light', icon: Sun03Icon, label: 'Light' },
    { value: 'dark', icon: Moon02Icon, label: 'Dark' },
    { value: 'system', icon: ComputerIcon, label: 'System' },
];

export function ThemeToggle() {
    const { appearance, resolvedAppearance, updateAppearance } =
        useAppearance();
    const currentIcon = resolvedAppearance === 'dark' ? Moon02Icon : Sun03Icon;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    aria-label="Change theme"
                    data-test="theme-toggle"
                >
                    <HugeiconsIcon icon={currentIcon} className="size-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {OPTIONS.map(({ value, icon, label }) => (
                    <DropdownMenuItem
                        key={value}
                        onClick={() => updateAppearance(value)}
                        className={cn(appearance === value && 'text-signal')}
                        data-test={`theme-toggle-${value}`}
                    >
                        <HugeiconsIcon icon={icon} className="size-4" />
                        {label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

import { HugeiconsIcon } from '@hugeicons/react';
import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarMenu className="gap-1">
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={isCurrentUrl(
                                item.href,
                                undefined,
                                item.matchPrefix,
                            )}
                            tooltip={{ children: item.title }}
                            className="rounded-xl data-[active=true]:bg-signal/10 data-[active=true]:text-signal data-[active=true]:hover:bg-signal/15 data-[active=true]:hover:text-signal"
                        >
                            <Link href={item.href} prefetch>
                                {item.icon && (
                                    <HugeiconsIcon icon={item.icon} size={20} />
                                )}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

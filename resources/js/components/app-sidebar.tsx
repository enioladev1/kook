import {
    FileSecurityIcon,
    Folder02Icon,
    Home01Icon,
    PlugSocketIcon,
    Settings02Icon,
} from '@hugeicons/core-free-icons';
import { Link } from '@inertiajs/react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as auditLogsIndex } from '@/routes/audit-logs';
import { index as projectsIndex } from '@/routes/projects';
import { index as providersIndex } from '@/routes/providers';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: Home01Icon,
    },
    {
        title: 'Projects',
        href: projectsIndex(),
        icon: Folder02Icon,
    },
    {
        title: 'Providers',
        href: providersIndex(),
        icon: PlugSocketIcon,
    },
    {
        title: 'Audit logs',
        href: auditLogsIndex(),
        icon: FileSecurityIcon,
    },
    {
        title: 'Settings',
        href: '/settings',
        icon: Settings02Icon,
        matchPrefix: true,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={dashboard()}
                                prefetch
                                className="gap-2.5"
                            >
                                <img
                                    src="/branding/favicon.png"
                                    alt=""
                                    className="size-7 shrink-0 rounded-md"
                                />
                                <span className="text-base font-bold tracking-tight group-data-[collapsible=icon]:hidden">
                                    Kook
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, BookOpenText } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Import PAGIBIG',
        href: '/remitted',
        icon: BookOpenText,
    },

    {
        title: 'Import GSS',
        href: '/gssremitted',
        icon: BookOpenText,
    },

   
];

const footerNavItems: NavItem[] = [
    {
        title: 'Identity Access Mgmt',
        href: '#',
        icon: Folder,
        isActive: true,
        items: [
            {
            title: 'PAGIBIGreport',
              href: '/PAGIBIGreport',
            },

            {
            title: 'GSSreport',
            href: '/GSSreport',
            },


            {
                title: 'Roles',
                href: '/iam/roles',
            },
            {
                title: 'Permissions',
                href: '/iam/permissions',
            },

            
        ],
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

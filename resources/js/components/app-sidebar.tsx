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
        title: 'Import PAGIBIG (contri)',
        href: '/remitted',
        icon: BookOpenText,
    },

    {
        title: 'Import PAGIBIG (CL)',
        href: '/remitted',
        icon: BookOpenText,
    },


    {
        title: 'Import PAGIBIG (MPL)',
        href: '/remitted',
        icon: BookOpenText,
    },

    {
        title: 'Import GSIS (contri & loan)',
        href: '/gssremitted',
        icon: BookOpenText,
    },

   
];

const footerNavItems: NavItem[] = [
    
    {
        title: 'Contribution Cerificate',
        href: '#',
        icon: Folder, // You can change this to another icon if preferred
        isActive: false,
        items: [
            {
            title: 'PAGIBIG report',
              href: '/PAGIBIGreport',
            },

            {
            title: 'GSIS report',
            href: '/GSSreport',
            },
        ],
    },
    
    
    {
        title: 'Loans Cerificate',
        href: '#',
        icon: Folder, // You can change this to another icon if preferred
        isActive: true,
        items: [
            {
                title: 'PAGIBIG loan report',
                href: '/loans/pagibig',
            },
            {
                title: 'GSIS loan report',
                href: '/GSSloan',
            },
        ],
    },
     
      
    {
    
        title: 'Identity Access Mgmt',
        href: '#',
        icon: Folder,
        isActive: false,
        items: [

            
            {
                title: 'Users',
                href: '/iam/users',
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

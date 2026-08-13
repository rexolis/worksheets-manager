<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, BookCheck, BookOpen, Users } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { classes, dashboard, sections, worksheets } from '@/routes';
import { showClass as sectionClass } from '@/routes/sections';
import { showClass as worksheetClass } from '@/routes/worksheets';
import type { NavItem } from '@/types';

const page = usePage();
const { isCurrentOrParentUrl } = useCurrentUrl();

const isAdmin = computed(() => page.props.auth.user?.is_admin === true);
const isTeacher = computed(() => page.props.auth.user?.is_teacher === true);
const isRegularUser = computed(
    () => !isAdmin.value && !isTeacher.value && page.props.auth.user != null,
);

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (isAdmin.value) {
        items.push({
            title: 'Worksheets',
            icon: BookCheck,
            isActive: isCurrentOrParentUrl(worksheets()),
            items: page.props.worksheetClasses.map((worksheetClassItem) => ({
                title: worksheetClassItem.name,
                href: worksheetClass(worksheetClassItem.slug),
            })),
        });
    }

    if (isAdmin.value || isTeacher.value) {
        items.push({
            title: 'Sections',
            icon: Users,
            isActive: isCurrentOrParentUrl(sections()),
            items: page.props.worksheetClasses.map((worksheetClassItem) => ({
                title: worksheetClassItem.name,
                href: sectionClass(worksheetClassItem.slug),
            })),
        });
    }

    if (isRegularUser.value) {
        items.push({
            title: 'Classes',
            href: classes(),
            icon: BookOpen,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

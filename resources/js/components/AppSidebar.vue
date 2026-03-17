<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    Calendar,
    ChartBar,
    LayoutGrid,
    Layers,
    Newspaper,
    Tags,
    Users,
    UserCheck,
    Church,
} from 'lucide-vue-next';
import { index as activitiesIndex } from '@/actions/App/Http/Controllers/ActivityController';
import { index as activityTypesIndex } from '@/actions/App/Http/Controllers/ActivityTypeController';
import { index as cellGroupsIndex } from '@/actions/App/Http/Controllers/CellGroupController';
import { index as classificationsIndex } from '@/actions/App/Http/Controllers/ClassificationController';
import { index as departmentsIndex } from '@/actions/App/Http/Controllers/DepartmentController';
import { index as ministriesIndex } from '@/actions/App/Http/Controllers/MinistryController';
import { index as participantsIndex } from '@/actions/App/Http/Controllers/ParticipantController';
import { index as pastorsIndex } from '@/actions/App/Http/Controllers/PastorController';
import { index as blogIndex } from '@/actions/App/Http/Controllers/PostController';
import { quarterlyReport } from '@/actions/App/Http/Controllers/ReportController';
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
import { dashboard } from '@/routes';
import { type NavGroup } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = page.props.auth.user;

const navGroups: NavGroup[] = [
    {
        label: 'Overview',
        items: [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        ],
    },
    {
        label: 'People',
        items: [
            { title: 'People of God', href: participantsIndex(), icon: Users },
            { title: 'Cell Groups', href: cellGroupsIndex(), icon: Layers },
            { title: 'Classifications', href: classificationsIndex(), icon: UserCheck },
        ],
    },
    {
        label: 'Organization',
        items: [
            { title: 'Ministries', href: ministriesIndex(), icon: Church },
            { title: 'Departments', href: departmentsIndex(), icon: Building2 },
            { title: 'Pastors', href: pastorsIndex(), icon: Users },
        ],
    },
    {
        label: 'Activities',
        items: [
            { title: 'Activities', href: activitiesIndex(), icon: Calendar },
            { title: 'Activity Types', href: activityTypesIndex(), icon: Tags },
        ],
    },
    {
        label: 'Reports & Content',
        items: [
            { title: 'Quarterly Report', href: quarterlyReport(), icon: ChartBar },
            { title: 'Blog Posts', href: blogIndex(), icon: Newspaper },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'View Church Website',
        href: '/',
        icon: BookOpen,
    },
];
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
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser v-if="user" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

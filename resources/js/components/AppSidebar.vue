<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Baby,
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
    ClipboardList,
    ListChecks,
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
import { index as usersIndex } from '@/actions/App/Http/Controllers/UserController';
import { index as siMembersIndex } from '@/actions/App/Http/Controllers/Si/SiMemberController';
import { index as siActivitiesIndex } from '@/actions/App/Http/Controllers/Si/SiActivityController';
import { index as siActivityCategoriesIndex } from '@/actions/App/Http/Controllers/Si/SiActivityCategoryController';
import { index as siReportsIndex } from '@/actions/App/Http/Controllers/Si/SiReportController';
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
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = page.props.auth.user;

const { canAny, isSuperAdmin } = usePermissions();

type NavItemWithPermission = {
    title: string;
    href: string;
    icon: unknown;
    permission?: string | null;
};

type NavGroupWithPermissions = {
    label: string;
    items: NavItemWithPermission[];
};

const allNavGroups: NavGroupWithPermissions[] = [
    {
        label: 'Overview',
        items: [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid, permission: null },
        ],
    },
    {
        label: 'People',
        items: [
            { title: 'People of God', href: participantsIndex(), icon: Users, permission: 'participants' },
            { title: 'Cell Groups', href: cellGroupsIndex(), icon: Layers, permission: 'cell_groups' },
            { title: 'Classifications', href: classificationsIndex(), icon: UserCheck, permission: 'classifications' },
        ],
    },
    {
        label: 'Organization',
        items: [
            { title: 'Ministries', href: ministriesIndex(), icon: Church, permission: 'ministries' },
            { title: 'Departments', href: departmentsIndex(), icon: Building2, permission: 'departments' },
            { title: 'Pastors', href: pastorsIndex(), icon: Users, permission: 'pastors' },
        ],
    },
    {
        label: 'Activities',
        items: [
            { title: 'Activities', href: activitiesIndex(), icon: Calendar, permission: 'activities' },
            { title: 'Activity Types', href: activityTypesIndex(), icon: Tags, permission: 'activity_types' },
        ],
    },
    {
        label: 'SI Program',
        items: [
            { title: 'SI Members', href: siMembersIndex(), icon: Baby, permission: 'si_members' },
            { title: 'SI Activities', href: siActivitiesIndex(), icon: ListChecks, permission: 'si_activities' },
            { title: 'Activity Categories', href: siActivityCategoriesIndex(), icon: Tags, permission: 'si_activity_categories' },
            { title: 'SI Report', href: siReportsIndex(), icon: ClipboardList, permission: 'si_activities' },
        ],
    },
    {
        label: 'Administration',
        items: [
            { title: 'Users', href: usersIndex(), icon: Users, permission: '__superAdmin' },
        ],
    },
    {
        label: 'Reports & Content',
        items: [
            { title: 'Quarterly Report', href: quarterlyReport(), icon: ChartBar, permission: 'activities' },
            { title: 'Blog Posts', href: blogIndex(), icon: Newspaper, permission: 'posts' },
        ],
    },
];

const navGroups = computed<NavGroup[]>(() => {
    return allNavGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => {
                if (item.permission === null) {
                    return true;
                }

                if (item.permission === '__superAdmin') {
                    return isSuperAdmin;
                }

                return canAny(item.permission);
            }),
        }))
        .filter((group) => group.items.length > 0);
});

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

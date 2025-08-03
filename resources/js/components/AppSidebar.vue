<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useClaudeSessions } from '@/composables/useClaudeSessions';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FileText, Folder, LayoutGrid, MessageSquare, Plus, GitBranch } from 'lucide-vue-next';
import { onMounted } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { claudeSessions, fetchSessions } = useClaudeSessions();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Claude',
        href: '/claude',
        icon: MessageSquare,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

onMounted(async () => {
    await fetchSessions();
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <SidebarGroup class="px-2 py-0">
                <div class="flex items-center justify-between">
                    <SidebarGroupLabel>Repositories</SidebarGroupLabel>
                    <button
                        class="h-5 w-5 rounded-sm hover:bg-accent hover:text-accent-foreground flex items-center justify-center transition-colors"
                        @click="$event => console.log('Add repository clicked')"
                    >
                        <Plus class="h-3 w-3" />
                    </button>
                </div>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton>
                            <GitBranch />
                            <span>No repositories yet</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0" v-if="claudeSessions.length > 0">
                <SidebarGroupLabel>Claude Sessions</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="session in claudeSessions" :key="session.filename">
                        <SidebarMenuButton as-child :is-active="page.url === `/claude/${session.filename}`" :tooltip="session.userMessage">
                            <Link :href="`/claude/${session.filename}`">
                                <FileText />
                                <span class="truncate">{{
                                    session.userMessage.length > 30 ? session.userMessage.substring(0, 30) + '...' : session.userMessage
                                }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
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
import { useRepositories } from '@/composables/useRepositories';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { BookOpen, FileText, Folder, GitBranch, LayoutGrid, Loader2, MessageSquare, Plus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { claudeSessions, fetchSessions } = useClaudeSessions();
const { repositories, fetchRepositories, cloneRepository, loading } = useRepositories();

const showCloneDialog = ref(false);
const repositoryUrl = ref('');
const branch = ref('');
const cloneError = ref('');

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
    await fetchRepositories();
});

const handleCloneRepository = async () => {
    cloneError.value = '';
    try {
        await cloneRepository(repositoryUrl.value, branch.value || undefined);
        showCloneDialog.value = false;
        repositoryUrl.value = '';
        branch.value = '';
    } catch (err: any) {
        cloneError.value = err.response?.data?.error || err.response?.data?.message || 'Failed to clone repository';
    }
};

const handleRepositoryClick = (repositoryName: string) => {
    router.visit(`/claude?repository=${encodeURIComponent(repositoryName)}`, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')" :preserve-scroll="true" :preserve-state="true">
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
                        class="flex h-5 w-5 items-center justify-center rounded-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                        @click="showCloneDialog = true"
                    >
                        <Plus class="h-3 w-3" />
                    </button>
                </div>
                <SidebarMenu>
                    <SidebarMenuItem v-if="repositories.length === 0">
                        <SidebarMenuButton>
                            <GitBranch />
                            <span>No repositories yet</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem v-for="repo in repositories" :key="repo.id">
                        <SidebarMenuButton as-child :tooltip="repo.url">
                            <a
                                :href="`/claude?repository=${encodeURIComponent(repo.name)}`"
                                @click.prevent="handleRepositoryClick(repo.name)"
                                class="cursor-pointer"
                            >
                                <GitBranch />
                                <span class="truncate">{{ repo.name }}</span>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0" v-if="claudeSessions.length > 0">
                <SidebarGroupLabel>Claude Sessions</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="session in claudeSessions" :key="session.filename">
                        <SidebarMenuButton as-child :is-active="page.url === `/claude/${session.filename}`" :tooltip="session.userMessage">
                            <Link
                                :href="
                                    session.repository
                                        ? `/claude/${session.filename}?repository=${encodeURIComponent(session.repository)}`
                                        : `/claude/${session.filename}`
                                "
                                :preserve-scroll="true"
                                :preserve-state="true"
                            >
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

    <Dialog v-model:open="showCloneDialog">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Clone Repository</DialogTitle>
                <DialogDescription> Enter the repository URL to clone it to your workspace. </DialogDescription>
            </DialogHeader>
            <div class="space-y-4 py-4">
                <div class="space-y-2">
                    <label for="repo-url" class="text-sm font-medium">Repository URL</label>
                    <Input
                        id="repo-url"
                        v-model="repositoryUrl"
                        placeholder="https://github.com/username/repository.git"
                        type="url"
                        :disabled="loading"
                    />
                </div>
                <div class="space-y-2">
                    <label for="branch" class="text-sm font-medium">Branch (optional)</label>
                    <Input id="branch" v-model="branch" placeholder="Leave empty for default branch" :disabled="loading" />
                </div>
                <div v-if="cloneError" class="text-sm text-destructive">
                    {{ cloneError }}
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showCloneDialog = false" :disabled="loading"> Cancel </Button>
                <Button @click="handleCloneRepository" :disabled="loading || !repositoryUrl">
                    <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                    Clone Repository
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <slot />
</template>

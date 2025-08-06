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
import { useConversations } from '@/composables/useConversations';
import { useRepositories } from '@/composables/useRepositories';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { AlertCircle, BookOpen, CheckCircle, Folder, GitBranch, Loader2, MessageSquare, MessageSquarePlus, Plus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { conversations, fetchConversations } = useConversations();
const { repositories, fetchRepositories, cloneRepository, loading, copyToHot } = useRepositories();

const showCloneDialog = ref(false);
const repositoryUrl = ref('');
const branch = ref('');
const cloneError = ref('');

const mainNavItems: NavItem[] = [
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
    await fetchRepositories();
    await fetchConversations();
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

const handleRepositoryClick = (repositorySlug: string) => {
    router.visit(`/repository/${repositorySlug}`, {
        preserveScroll: true,
        preserveState: true,
    });
};

const handleCopyToHot = async (repositoryId: number) => {
    console.log('handleCopyToHot called with id:', repositoryId);

    if (!repositoryId) {
        console.error('No repository ID provided');
        return;
    }

    try {
        const response = await copyToHot(repositoryId);
        console.log('Response from copyToHot:', response);

        if (response.has_hot_folder) {
            console.log('Hot folder already exists');
        } else {
            console.log('Copy job dispatched successfully');
            // Refresh repositories after a delay to check status
            setTimeout(() => {
                console.log('Refreshing repositories...');
                fetchRepositories();
            }, 3000);
        }
    } catch (error) {
        console.error('Failed to copy repository to hot folder:', error);
        alert('Failed to copy repository to hot folder. Check console for details.');
    }
};
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('claude')" :preserve-scroll="true" :preserve-state="true">
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
                        <SidebarMenuButton @click="handleRepositoryClick(repo.slug)" :tooltip="repo.url">
                            <GitBranch />
                            <span class="flex-1 truncate">{{ repo.name }}</span>
                            <div v-if="repo.has_hot_folder" class="ml-auto" title="Hot folder ready">
                                <CheckCircle class="h-3.5 w-3.5 text-green-500" />
                            </div>
                            <div
                                v-else
                                class="ml-auto cursor-pointer transition-transform hover:scale-110"
                                @click.stop="() => handleCopyToHot(repo.id)"
                                title="Click to copy to hot folder"
                            >
                                <AlertCircle class="h-3.5 w-3.5 text-yellow-500" />
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0" v-if="conversations.length > 0">
                <SidebarGroupLabel>Conversations</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="conversation in conversations" :key="conversation.id">
                        <SidebarMenuButton as-child :is-active="page.url === `/claude/conversation/${conversation.id}`">
                            <Link :href="`/claude/conversation/${conversation.id}`" :preserve-scroll="true" :preserve-state="true">
                                <MessageSquarePlus />
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate">{{ conversation.title }}</span>
                                    <div class="mt-0.5 flex items-center justify-between gap-1 text-xs text-muted-foreground">
                                        <div v-if="conversation.repository" class="flex min-w-0 items-center gap-1">
                                            <GitBranch class="h-3 w-3 shrink-0" />
                                            <span class="truncate">{{ conversation.repository }}</span>
                                        </div>
                                        <span v-else></span>
                                        <span v-if="conversation.project_directory" class="truncate text-right">{{
                                            conversation.project_directory
                                        }}</span>
                                    </div>
                                </div>
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

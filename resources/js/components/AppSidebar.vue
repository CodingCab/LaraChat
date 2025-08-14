<script setup lang="ts">
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
import { Link, router, usePage } from '@inertiajs/vue3';
import { GitBranch, Loader2, MessageSquarePlus, Plus } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { conversations, fetchConversations, cleanup } = useConversations();
const { repositories, fetchRepositories, cloneRepository, loading } = useRepositories();

const showCloneDialog = ref(false);
const repositoryUrl = ref('');
const branch = ref('');
const cloneError = ref('');

onMounted(async () => {
    await fetchRepositories();
    await fetchConversations(false, true); // Force initial fetch
});

onUnmounted(() => {
    cleanup(); // Clean up the refresh interval when component unmounts
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
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup class="px-2 py-0" v-if="conversations.length > 0">
                <SidebarGroupLabel>Conversations</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="conversation in conversations" :key="conversation.id" class="mb-1">
                        <SidebarMenuButton as-child :is-active="page.url === `/claude/conversation/${conversation.id}`">
                            <Link
                                :href="`/claude/conversation/${conversation.id}`"
                                :preserve-scroll="true"
                                :preserve-state="true"
                                class="flex items-center"
                            >
                                <MessageSquarePlus />
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate">{{ conversation.title }}</span>
                                    <div class="mt-0.5 flex items-center justify-between gap-1 text-xs text-muted-foreground">
                                        <div v-if="conversation.repository" class="flex min-w-0 items-center gap-1">
                                            <GitBranch class="h-3 w-3 shrink-0" />
                                            <span class="truncate">{{ conversation.repository }}</span>
                                        </div>
                                        <span v-else></span>
                                        <a
                                            v-if="conversation.project_directory"
                                            :href="`https://${conversation.project_directory.split('/').pop()}.larachat-restricted.coding.cab`"
                                            target="_blank"
                                            class="truncate text-right hover:underline"
                                            @click.stop
                                        >
                                            {{ conversation.project_directory.split('/').pop() }}
                                        </a>
                                    </div>
                                </div>
                                <Loader2 v-if="conversation.is_processing" class="ml-auto h-3 w-3 animate-spin text-muted-foreground" />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
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

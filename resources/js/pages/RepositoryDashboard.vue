<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { GitBranch, FileCode, FolderOpen, Clock, Activity, MessageSquare, Copy, Terminal } from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps<{
    repository: {
        id: number;
        name: string;
        url: string;
        branch?: string;
        path: string;
        has_hot_folder: boolean;
        created_at: string;
        updated_at: string;
    };
    stats?: {
        files_count: number;
        directories_count: number;
        total_size: string;
        last_commit?: string;
    };
    recent_conversations?: Array<{
        id: number;
        title: string;
        created_at: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Repositories', href: '/repositories' },
    { title: props.repository.name },
];

const fileTree = ref<any[]>([]);
const loadingTree = ref(false);

const fetchFileTree = async () => {
    loadingTree.value = true;
    try {
        const response = await axios.get(`/api/repositories/${props.repository.id}/files`);
        fileTree.value = response.data.tree || [];
    } catch (error) {
        console.error('Failed to fetch file tree:', error);
    } finally {
        loadingTree.value = false;
    }
};

const copyToHot = async () => {
    try {
        await axios.post(`/api/repositories/${props.repository.id}/copy-to-hot`);
        router.reload({ only: ['repository'] });
    } catch (error) {
        console.error('Failed to copy to hot folder:', error);
    }
};

const startChatSession = () => {
    router.visit(`/claude?repository=${encodeURIComponent(props.repository.name)}`);
};

const openConversation = (conversationId: number) => {
    router.visit(`/claude/conversation/${conversationId}`);
};

onMounted(() => {
    fetchFileTree();
});

const formattedDate = computed(() => {
    return new Date(props.repository.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6 space-y-6">
            <!-- Repository Header -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-3">
                        <GitBranch class="h-8 w-8" />
                        {{ repository.name }}
                    </h1>
                    <p class="mt-2 text-muted-foreground">{{ repository.url }}</p>
                    <div class="mt-3 flex items-center gap-4">
                        <Badge variant="outline" v-if="repository.branch">
                            {{ repository.branch }}
                        </Badge>
                        <Badge v-if="repository.has_hot_folder" variant="default" class="bg-green-500">
                            Hot Folder Ready
                        </Badge>
                        <Badge v-else variant="secondary">
                            Not in Hot Folder
                        </Badge>
                        <span class="text-sm text-muted-foreground flex items-center gap-1">
                            <Clock class="h-3 w-3" />
                            Cloned {{ formattedDate }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button 
                        v-if="!repository.has_hot_folder" 
                        @click="copyToHot"
                        variant="outline"
                    >
                        <Copy class="mr-2 h-4 w-4" />
                        Copy to Hot Folder
                    </Button>
                    <Button @click="startChatSession">
                        <MessageSquare class="mr-2 h-4 w-4" />
                        Start Chat Session
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-4" v-if="stats">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Files</CardTitle>
                        <FileCode class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.files_count }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Directories</CardTitle>
                        <FolderOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.directories_count }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Repository Size</CardTitle>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_size }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Conversations</CardTitle>
                        <MessageSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ recent_conversations?.length || 0 }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Tabs -->
            <Tabs defaultValue="overview" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="overview">Overview</TabsTrigger>
                    <TabsTrigger value="files">Files</TabsTrigger>
                    <TabsTrigger value="conversations">Conversations</TabsTrigger>
                    <TabsTrigger value="actions">Actions</TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Repository Information</CardTitle>
                            <CardDescription>Details about this repository</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Repository Path</p>
                                    <p class="text-sm font-mono">{{ repository.path }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Clone URL</p>
                                    <p class="text-sm font-mono">{{ repository.url }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Branch</p>
                                    <p class="text-sm">{{ repository.branch || 'main' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Status</p>
                                    <p class="text-sm">
                                        {{ repository.has_hot_folder ? 'Available for AI assistance' : 'Need to copy to hot folder' }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="recent_conversations && recent_conversations.length > 0">
                        <CardHeader>
                            <CardTitle>Recent Conversations</CardTitle>
                            <CardDescription>Latest AI chat sessions for this repository</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div 
                                    v-for="conversation in recent_conversations.slice(0, 5)" 
                                    :key="conversation.id"
                                    class="flex items-center justify-between p-2 rounded-lg hover:bg-accent cursor-pointer"
                                    @click="openConversation(conversation.id)"
                                >
                                    <div class="flex items-center gap-2">
                                        <MessageSquare class="h-4 w-4" />
                                        <span class="text-sm font-medium">{{ conversation.title }}</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        {{ new Date(conversation.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="files" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>File Structure</CardTitle>
                            <CardDescription>Browse repository files and directories</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="loadingTree" class="text-center py-8 text-muted-foreground">
                                Loading file tree...
                            </div>
                            <div v-else-if="fileTree.length > 0" class="font-mono text-sm">
                                <pre class="overflow-x-auto">{{ fileTree.join('\n') }}</pre>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                No files found or unable to load file tree
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="conversations" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>All Conversations</CardTitle>
                            <CardDescription>Complete history of AI conversations for this repository</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recent_conversations && recent_conversations.length > 0" class="space-y-2">
                                <div 
                                    v-for="conversation in recent_conversations" 
                                    :key="conversation.id"
                                    class="flex items-center justify-between p-3 rounded-lg border hover:bg-accent cursor-pointer"
                                    @click="openConversation(conversation.id)"
                                >
                                    <div>
                                        <p class="font-medium">{{ conversation.title }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ new Date(conversation.created_at).toLocaleString() }}
                                        </p>
                                    </div>
                                    <Button variant="ghost" size="sm">
                                        Open
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                No conversations yet. Start a chat session to begin.
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="actions" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Repository Actions</CardTitle>
                            <CardDescription>Available operations for this repository</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <p class="font-medium">Copy to Hot Folder</p>
                                    <p class="text-sm text-muted-foreground">
                                        Make repository available for AI assistance
                                    </p>
                                </div>
                                <Button 
                                    @click="copyToHot" 
                                    :disabled="repository.has_hot_folder"
                                    variant="outline"
                                >
                                    <Copy class="mr-2 h-4 w-4" />
                                    {{ repository.has_hot_folder ? 'Already Copied' : 'Copy Now' }}
                                </Button>
                            </div>
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <p class="font-medium">Start AI Chat</p>
                                    <p class="text-sm text-muted-foreground">
                                        Open Claude assistant for this repository
                                    </p>
                                </div>
                                <Button @click="startChatSession">
                                    <MessageSquare class="mr-2 h-4 w-4" />
                                    Start Chat
                                </Button>
                            </div>
                            <div class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <p class="font-medium">Open in Terminal</p>
                                    <p class="text-sm text-muted-foreground">
                                        Repository path: {{ repository.path }}
                                    </p>
                                </div>
                                <Button variant="outline" disabled>
                                    <Terminal class="mr-2 h-4 w-4" />
                                    Open Terminal
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
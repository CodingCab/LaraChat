<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Activity, ArrowRight, Clock, Copy, FileCode, FolderOpen, GitBranch, MessageSquare, Send, Sparkles, Terminal } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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


const fileTree = ref<any[]>([]);
const loadingTree = ref(false);
const messageInput = ref('');

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

const startChatWithMessage = (message?: string) => {
    const finalMessage = message || messageInput.value.trim();
    if (finalMessage) {
        // Use router.get with data to properly send parameters
        router.get('/claude/new', {
            message: finalMessage,
            repository: props.repository.name
        });
    }
};

const quickMessages = [
    { text: 'Show this week tasks', icon: '📋' },
    { text: 'Let me ask you about', icon: '💬' },
    { text: 'Review recent changes', icon: '🔍' },
    { text: 'Help me debug an issue', icon: '🐛' },
];

onMounted(() => {
    fetchFileTree();
});

const formattedDate = computed(() => {
    return new Date(props.repository.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
});
</script>

<template>
    <AppLayout>
        <div class="container mx-auto py-6">
            <!-- Main CTA Section -->
            <div class="flex min-h-[60vh] flex-col items-center justify-center">
                <!-- Conversation Starter -->
                <div class="w-full max-w-2xl space-y-6">
                    <div class="text-center">
                        <div class="mb-2 flex items-center justify-center">
                            <Sparkles class="h-8 w-8 text-primary" />
                        </div>
                        <h2 class="text-2xl font-semibold">Start a conversation</h2>
                        <p class="mt-2 text-muted-foreground">Ask Claude about your {{ repository.name }} codebase</p>
                    </div>

                    <!-- Main Input -->
                    <div class="relative">
                        <Input
                            v-model="messageInput"
                            placeholder="Type your message or question..."
                            @keyup.enter="startChatWithMessage()"
                            class="h-14 pl-5 pr-14 text-base"
                        />
                        <Button
                            @click="startChatWithMessage()"
                            :disabled="!messageInput.trim()"
                            size="icon"
                            class="absolute right-2 top-1/2 h-10 w-10 -translate-y-1/2 rounded-full"
                        >
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- Quick Messages -->
                    <div class="space-y-3">
                        <p class="text-center text-sm text-muted-foreground">Or start with:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <Button
                                v-for="(message, index) in quickMessages"
                                :key="index"
                                @click="startChatWithMessage(message.text)"
                                variant="outline"
                                size="sm"
                                class="group"
                            >
                                <span class="mr-2">{{ message.icon }}</span>
                                {{ message.text }}
                                <ArrowRight class="ml-2 h-3 w-3 transition-transform group-hover:translate-x-0.5" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Minimal Stats -->
                <div class="mt-12 flex items-center gap-8 text-sm text-muted-foreground" v-if="stats">
                    <div class="flex items-center gap-2">
                        <FileCode class="h-4 w-4" />
                        <span>{{ stats.files_count }} files</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <MessageSquare class="h-4 w-4" />
                        <span>{{ recent_conversations?.length || 0 }} conversations</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Activity class="h-4 w-4" />
                        <span>{{ stats.total_size }}</span>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

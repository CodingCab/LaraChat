<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, nextTick, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Send } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Claude', href: '/claude' },
];

interface Message {
    id: number;
    content: string;
    role: 'user' | 'assistant';
    timestamp: Date;
}

const messages = ref<Message[]>([]);
const inputMessage = ref('');
const isLoading = ref(false);
const messagesContainer = ref<HTMLElement>();
const textareaRef = ref<HTMLTextAreaElement>();

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const adjustTextareaHeight = () => {
    nextTick(() => {
        const textarea = textareaRef.value?.$el?.querySelector('textarea');
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
        }
    });
};

const sendMessage = async () => {
    if (!inputMessage.value.trim() || isLoading.value) return;

    const userMessage: Message = {
        id: Date.now(),
        content: inputMessage.value,
        role: 'user',
        timestamp: new Date(),
    };

    messages.value.push(userMessage);
    const messageToSend = inputMessage.value;
    inputMessage.value = '';
    
    nextTick(() => {
        const textarea = textareaRef.value?.$el?.querySelector('textarea');
        if (textarea) {
            textarea.style.height = 'auto';
        }
    });

    isLoading.value = true;
    await scrollToBottom();

    const assistantMessage: Message = {
        id: Date.now() + 1,
        content: '',
        role: 'assistant',
        timestamp: new Date(),
    };
    messages.value.push(assistantMessage);

    try {
        const response = await fetch('/api/claude', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ prompt: messageToSend }),
        });

        if (!response.ok) {
            throw new Error('Failed to send message');
        }

        if (!response.body) {
            throw new Error('No response body');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            assistantMessage.content += chunk;
            await scrollToBottom();
        }
    } catch (error) {
        console.error('Error sending message:', error);
        assistantMessage.content = 'Sorry, I encountered an error. Please try again.';
    } finally {
        isLoading.value = false;
        await scrollToBottom();
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

const formatTime = (date: Date) => {
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

onMounted(() => {
    nextTick(() => {
        const textarea = textareaRef.value?.$el?.querySelector('textarea');
        if (textarea) {
            textarea.focus();
        }
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-gray-50 dark:bg-gray-900">
            <!-- Chat Messages -->
            <ScrollArea ref="messagesContainer" class="flex-1 p-4">
                <div class="mx-auto max-w-3xl space-y-4">
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        :class="[
                            'flex',
                            message.role === 'user' ? 'justify-end' : 'justify-start',
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[70%] rounded-2xl px-4 py-2 shadow-sm',
                                message.role === 'user'
                                    ? 'bg-green-500 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
                            ]"
                        >
                            <p class="whitespace-pre-wrap break-words">{{ message.content }}</p>
                            <p
                                :class="[
                                    'mt-1 text-xs',
                                    message.role === 'user'
                                        ? 'text-green-100'
                                        : 'text-gray-500 dark:text-gray-400',
                                ]"
                            >
                                {{ formatTime(message.timestamp) }}
                            </p>
                        </div>
                    </div>
                    <div v-if="isLoading" class="flex justify-start">
                        <div class="max-w-[70%] rounded-2xl bg-white dark:bg-gray-800 px-4 py-2 shadow-sm">
                            <div class="flex space-x-1">
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.3s]"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.15s]"></div>
                                <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <!-- Input Area -->
            <div class="border-t bg-white dark:bg-gray-800 p-4">
                <div class="mx-auto max-w-3xl">
                    <div class="flex items-end space-x-2">
                        <Textarea
                            ref="textareaRef"
                            v-model="inputMessage"
                            @keydown="handleKeydown"
                            @input="adjustTextareaHeight"
                            placeholder="Type a message..."
                            class="min-h-[40px] max-h-[120px] resize-none overflow-y-auto"
                            :rows="1"
                            :disabled="isLoading"
                        />
                        <Button
                            @click="sendMessage"
                            :disabled="!inputMessage.trim() || isLoading"
                            size="icon"
                            class="h-10 w-10 rounded-full"
                        >
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
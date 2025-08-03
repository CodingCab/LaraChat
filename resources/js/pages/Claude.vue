<script setup lang="ts">
import ChatMessage from '@/components/ChatMessage.vue';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Textarea } from '@/components/ui/textarea';
import { useChatMessages } from '@/composables/useChatMessages';
import { useChatUI } from '@/composables/useChatUI';
import { useClaudeApi } from '@/composables/useClaudeApi';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { extractTextFromResponses } from '@/utils/claudeResponseParser';
import { Send } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    sessionFile?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Claude', href: '/claude' }];

// Composables
const { messagesContainer, textareaRef, scrollToBottom, adjustTextareaHeight, resetTextareaHeight, focusInput, setupFocusHandlers } = useChatUI();
const { messages, addUserMessage, addAssistantMessage, appendToMessage, formatTime } = useChatMessages();
const { isLoading, sendMessageToApi, loadSession } = useClaudeApi();

// Local state
const inputMessage = ref('');
const sessionFilename = ref<string | null>(null);
const sessionId = ref<string | null>(null);
const showRawResponses = ref(false);

// Setup focus handlers
setupFocusHandlers(isLoading);

const initializeSession = () => {
    if (!sessionId.value) {
        sessionId.value = 'generated-' + Date.now().toString(36);
    }

    if (!sessionFilename.value) {
        if (props.sessionFile) {
            sessionFilename.value = props.sessionFile;
        } else {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').substring(0, 19);
            sessionFilename.value = `${timestamp}-sessionId-${sessionId.value}.json`;
        }
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim() || isLoading.value) return;

    // Add user message
    const messageToSend = inputMessage.value;
    addUserMessage(messageToSend);

    // Clear input
    inputMessage.value = '';
    resetTextareaHeight();

    // Start loading
    isLoading.value = true;
    await scrollToBottom();

    // Add assistant message placeholder
    const assistantMessage = addAssistantMessage();

    // Initialize session
    initializeSession();

    try {
        await sendMessageToApi(
            {
                prompt: messageToSend,
                sessionId: sessionId.value!,
                sessionFilename: sessionFilename.value!,
            },
            (text, rawResponse) => {
                appendToMessage(assistantMessage.id, text, rawResponse);
                scrollToBottom();
            },
        );
    } catch (error) {
        console.error('Error sending message:', error);
        appendToMessage(assistantMessage.id, 'Sorry, I encountered an error. Please try again.');
    } finally {
        isLoading.value = false;
        await scrollToBottom();
        focusInput(false);
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

const loadSessionMessages = async () => {
    if (!props.sessionFile) return;

    try {
        const sessionData = await loadSession(props.sessionFile);

        // Process each conversation
        for (const conversation of sessionData) {
            // Add user message
            messages.value.push({
                id: Date.now() + Math.random(),
                content: conversation.userMessage,
                role: 'user',
                timestamp: new Date(conversation.timestamp),
            });

            if (conversation.rawJsonResponses?.length) {
                for (const rawResponse of conversation.rawJsonResponses) {
                    messages.value.push({
                        id: Date.now() + Math.random() + 1,
                        content: extractTextFromResponses(rawResponse),
                        role: 'assistant',
                        timestamp: new Date(conversation.timestamp),
                        rawResponses: rawResponse,
                    });
                }
            }
        }

        // Set session info
        sessionFilename.value = props.sessionFile;
        if (sessionData.length > 0 && sessionData[0].sessionId) {
            sessionId.value = sessionData[0].sessionId;
        }

        await scrollToBottom();
    } catch (error) {
        console.error('Error loading session messages:', error);
    }
};

onMounted(async () => {
    await loadSessionMessages();
    focusInput(false);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Debug Toggle -->
        <div class="fixed top-20 right-4 z-50">
            <Button @click="showRawResponses = !showRawResponses" variant="outline" size="sm">
                {{ showRawResponses ? 'Hide' : 'Show' }} Raw Responses
            </Button>
        </div>
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-gray-50 dark:bg-gray-900">
            <!-- Chat Messages -->
            <ScrollArea ref="messagesContainer" class="flex-1 p-4">
                <div class="mx-auto max-w-3xl space-y-4">
                    <ChatMessage
                        v-for="message in messages"
                        :key="message.id"
                        :message="message"
                        :format-time="formatTime"
                        :show-raw-responses="showRawResponses"
                    />

                    <div v-if="isLoading" class="flex justify-start">
                        <div class="max-w-[70%] rounded-2xl bg-white px-4 py-2 shadow-sm dark:bg-gray-800">
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
            <div class="border-t bg-white p-4 dark:bg-gray-800">
                <div class="mx-auto max-w-3xl">
                    <div class="flex items-end space-x-2">
                        <Textarea
                            ref="textareaRef"
                            v-model="inputMessage"
                            @keydown="handleKeydown"
                            @input="adjustTextareaHeight"
                            placeholder="Type a message..."
                            class="max-h-[120px] min-h-[40px] resize-none overflow-y-auto"
                            :rows="1"
                            :disabled="isLoading"
                        />
                        <Button @click="sendMessage" :disabled="!inputMessage.trim() || isLoading" size="icon" class="h-10 w-10 rounded-full">
                            <Send class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

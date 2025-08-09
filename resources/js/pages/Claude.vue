<script setup lang="ts">
import ChatMessage from '@/components/ChatMessage.vue';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Textarea } from '@/components/ui/textarea';
import { useChatMessages } from '@/composables/useChatMessages';
import { useChatUI } from '@/composables/useChatUI';
import { useClaudeApi } from '@/composables/useClaudeApi';
import { useClaudeSessions } from '@/composables/useClaudeSessions';
import { useConversations } from '@/composables/useConversations';
import { useRepositories } from '@/composables/useRepositories';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { extractTextFromResponse } from '@/utils/claudeResponseParser';
import { router } from '@inertiajs/vue3';
import { Eye, EyeOff, GitBranch, Send } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

// Constants
const POLLING_INTERVAL_MS = 2000;
const POLLING_INTERVAL_SLOW_MS = 5000;
const SCROLL_DELAY_MS = 150;
const SCROLL_RETRY_DELAY_MS = 200;
const REFRESH_DELAY_MS = 500;
const SESSION_REFRESH_DELAY_MS = 1000;

const props = defineProps<{
    sessionFile?: string;
    repository?: string;
    conversationId?: number;
    sessionId?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Claude', href: '/claude' }];

// Composables
const { messagesContainer, textareaRef, scrollToBottom, isAtBottom, adjustTextareaHeight, resetTextareaHeight, focusInput, setupFocusHandlers } = useChatUI();
const { messages, addUserMessage, addAssistantMessage, appendToMessage, formatTime } = useChatMessages();
const { isLoading, sendMessageToApi, loadSession } = useClaudeApi();
const { claudeSessions, refreshSessions } = useClaudeSessions();
const { fetchConversations } = useConversations();
const { repositories, fetchRepositories } = useRepositories();

// Local state
const inputMessage = ref('');
const sessionFilename = ref<string | null>(props.sessionFile || null);
const sessionId = ref<string | null>(props.sessionId || null);
const conversationId = ref<number | null>(props.conversationId || null);
const hideSystemMessages = ref(true);
const selectedRepository = ref<string | null>(props.repository || null);

// Polling state
const pollingInterval = ref<number | null>(null);
const incompleteMessageFound = ref(false);

// Setup focus handlers
setupFocusHandlers(isLoading);

// Computed properties
const selectedRepositoryData = computed(() => {
    if (!selectedRepository.value) return null;
    return repositories.value.find((r) => {
        if (r.local_path?.endsWith('/' + selectedRepository.value)) return true;
        return r.name.toLowerCase() === selectedRepository.value?.toLowerCase();
    });
});

const filteredMessages = computed(() => {
    if (!hideSystemMessages.value) return messages.value;

    return messages.value.filter((message) => {
        if (message.role === 'user') return true;

        if (message.rawResponses?.length > 0) {
            return message.rawResponses.some((response) => {
                if (response.type === 'assistant' && response.message?.content) {
                    return response.message.content.some((item: any) => item.type === 'text');
                }
                return false;
            });
        }
        return true;
    });
});

// Utility functions
const generateSessionFilename = () => {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').substring(0, 19);
    const tempId = Date.now().toString(36);
    return `${timestamp}-session-${tempId}.json`;
};

const extractSessionId = (responses: any[]) => {
    for (const response of responses) {
        if (response.type === 'system' && response.subtype === 'init' && response.session_id) {
            return response.session_id;
        }
    }
    return null;
};

const extractRepositoryFromPath = (path: string) => {
    const pathParts = path.split('/');
    return pathParts[pathParts.length - 1];
};

const delayedScroll = async (force = false) => {
    await nextTick();
    setTimeout(async () => {
        await scrollToBottom(force);
        setTimeout(() => scrollToBottom(force), SCROLL_RETRY_DELAY_MS);
    }, SCROLL_DELAY_MS);
};

// Polling management
const startPolling = (interval = POLLING_INTERVAL_MS) => {
    stopPolling();
    console.log(`Starting polling with ${interval}ms interval...`);
    pollingInterval.value = window.setInterval(() => {
        if (props.sessionFile) {
            loadSessionMessages(true);
        } else if (props.conversationId) {
            loadConversationMessages();
        }
    }, interval);
};

const stopPolling = () => {
    if (pollingInterval.value) {
        console.log('Stopping polling...');
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

// Message handling
const processConversationResponses = (conversation: any, isPolling = false) => {
    const messagesList = [];

    // Handle initial session format with role: 'user'
    if (conversation.role === 'user') {
        if (!isPolling || messages.value.length === 0) {
            messagesList.push({
                id: Date.now() + Math.random(),
                content: conversation.userMessage || '',
                role: 'user',
                timestamp: new Date(conversation.timestamp),
            });
        }
        return messagesList;
    }

    // Handle normal conversation format
    if (!isPolling || messages.value.length === 0) {
        messagesList.push({
            id: Date.now() + Math.random(),
            content: conversation.userMessage || '',
            role: 'user',
            timestamp: new Date(conversation.timestamp),
        });
    }

    if (conversation.rawJsonResponses?.length) {
        // Handle rawJsonResponses as an array of strings (JSON strings that need parsing)
        conversation.rawJsonResponses.forEach((rawResponseStr: any, i: number) => {
            let rawResponse: any;

            // Parse if it's a string, otherwise use as-is
            if (typeof rawResponseStr === 'string') {
                try {
                    rawResponse = JSON.parse(rawResponseStr);
                } catch (e) {
                    console.error('Failed to parse raw response:', e);
                    rawResponse = { type: 'error', content: rawResponseStr };
                }
            } else {
                rawResponse = rawResponseStr;
            }

            const content = extractTextFromResponse(rawResponse);
            messagesList.push({
                id: Date.now() + Math.random() + i,
                content: content || `[${rawResponse.type || 'unknown'} response]`,
                role: 'assistant',
                timestamp: new Date(conversation.timestamp),
                rawResponses: [rawResponse],
            });
        });
    }

    return messagesList;
};

const loadSessionMessages = async (isPolling = false) => {
    if (!props.sessionFile) return;

    try {
        const sessionData = await loadSession(props.sessionFile);
        console.log('Session data loaded:', sessionData);
        incompleteMessageFound.value = false;

        isPolling = false;

        if (!isPolling) {
            console.log('Is not polling, processing session data...');
            messages.value = [];

            for (const conversation of sessionData) {
                console.log('Processing conversation:', conversation);
                if (!conversation.isComplete) {
                    incompleteMessageFound.value = true;
                }
                const processedMessages = processConversationResponses(conversation);
                console.log('Processed messages:', processedMessages);
                messages.value.push(...processedMessages);
            }

            // Extract session metadata
            if (sessionData.length > 0) {
                const lastConversation = sessionData[sessionData.length - 1];
                const extractedSessionId = extractSessionId(lastConversation.rawJsonResponses || []);
                if (extractedSessionId) sessionId.value = extractedSessionId;

                if (!selectedRepository.value) {
                    for (const conversation of sessionData) {
                        if (conversation.repositoryPath) {
                            selectedRepository.value = extractRepositoryFromPath(conversation.repositoryPath);
                            break;
                        }
                    }
                }
            }
        } else {
            console.log('Polling mode, updating messages...');
            // Handle polling updates for incomplete conversations
            if (sessionData.length > 0) {
                const lastConversation = sessionData[sessionData.length - 1];
                if (!lastConversation.isComplete) {
                    incompleteMessageFound.value = true;

                    // Update only if we have new responses
                    const existingResponseCount = messages.value.filter(m => m.role === 'assistant').length;
                    const newResponses = lastConversation.rawJsonResponses?.slice(existingResponseCount) || [];

                    newResponses.forEach((rawResponse: any, i: number) => {
                        const content = extractTextFromResponse(rawResponse);
                        messages.value.push({
                            id: Date.now() + Math.random() + i,
                            content: content || `[${rawResponse.type || 'unknown'} response]`,
                            role: 'assistant',
                            timestamp: new Date(lastConversation.timestamp),
                            rawResponses: [rawResponse],
                        });
                    });
                }
            }
        }

        sessionFilename.value = props.sessionFile;
        console.log('Final messages.value:', messages.value);
        console.log('Final filteredMessages.value:', filteredMessages.value);
        await delayedScroll(false);

        // Manage polling based on completion status
        if (incompleteMessageFound.value && !pollingInterval.value) {
            startPolling();
        } else if (!incompleteMessageFound.value && pollingInterval.value) {
            stopPolling();
        }
    } catch (error) {
        console.error('Error loading session messages:', error);
    }
};

const loadConversationMessages = async () => {
    if (!props.conversationId) return;

    try {
        const response = await fetch(`/api/conversations/${props.conversationId}/messages`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            }
        });
        const data = await response.json();

        console.log('Loading conversation messages:', data);

        // Clear and rebuild messages array
        const newMessages = [];
        let hasStreamingMessage = false;

        for (const msg of data.messages) {
            if (msg.role === 'user') {
                newMessages.push({
                    id: msg.id || Date.now() + Math.random(),
                    content: msg.content || '',
                    role: 'user',
                    timestamp: new Date(msg.created_at),
                });
            } else if (msg.role === 'assistant') {
                newMessages.push({
                    id: msg.id || Date.now() + Math.random(),
                    content: msg.content || '',
                    role: 'assistant',
                    timestamp: new Date(msg.created_at),
                    rawResponses: [],
                });
                if (msg.is_streaming) hasStreamingMessage = true;
            }
        }

        // Update messages array
        messages.value = newMessages;

        console.log('Messages loaded:', messages.value);
        console.log('Filtered messages:', filteredMessages.value);

        // Check if we're waiting for an assistant response
        const hasUserMessage = newMessages.some(m => m.role === 'user');
        const hasAssistantResponse = newMessages.some(m => m.role === 'assistant' && m.content);
        
        // Show loading if we have a user message but no assistant response yet
        if (hasUserMessage && !hasAssistantResponse) {
            isLoading.value = true;
        } else {
            isLoading.value = false;
        }

        // Adjust polling frequency based on streaming status
        if (hasStreamingMessage) {
            startPolling(POLLING_INTERVAL_MS);
        } else if (data.messages.length > 0) {
            // Only continue slow polling if conversation might still be active
            // Stop after messages are complete
            const lastMessage = data.messages[data.messages.length - 1];
            if (lastMessage && lastMessage.role === 'assistant' && lastMessage.content) {
                // Assistant has responded, we can stop polling eventually
                isLoading.value = false;
                if (!pollingInterval.value) {
                    // Do one more poll cycle to ensure we got everything
                    startPolling(POLLING_INTERVAL_SLOW_MS);
                    setTimeout(() => stopPolling(), POLLING_INTERVAL_SLOW_MS * 2);
                }
            } else {
                // Still waiting for response
                isLoading.value = true;
                startPolling(POLLING_INTERVAL_SLOW_MS);
            }
        }

        await nextTick();
        await scrollToBottom(false);
    } catch (error) {
        console.error('Error loading conversation messages:', error);
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim() || isLoading.value) return;

    const messageToSend = inputMessage.value;
    addUserMessage(messageToSend);
    inputMessage.value = '';
    resetTextareaHeight();
    isLoading.value = true;
    await scrollToBottom(true); // Force scroll when user sends a message

    // Initialize session if needed
    if (!sessionFilename.value) {
        sessionFilename.value = props.sessionFile || generateSessionFilename();

        // Add to sessions list immediately for new sessions
        if (!props.sessionFile) {
            const existingSession = claudeSessions.value.find(s => s.filename === sessionFilename.value);
            if (!existingSession) {
                claudeSessions.value.unshift({
                    filename: sessionFilename.value,
                    name: messageToSend.substring(0, 30) + (messageToSend.length > 30 ? '...' : ''),
                    userMessage: messageToSend,
                    repository: selectedRepository.value || undefined,
                    path: `/claude/${sessionFilename.value}`,
                    lastModified: Date.now(),
                });
            }
        }
    }

    try {
        const result = await sendMessageToApi(
            {
                prompt: messageToSend,
                sessionId: sessionId.value || undefined,
                sessionFilename: sessionFilename.value,
                repositoryPath: selectedRepositoryData.value?.local_path,
                conversationId: conversationId.value || undefined,
            },
            (text, rawResponse) => {
                // Extract session ID from init response
                if (rawResponse?.type === 'system' && rawResponse.subtype === 'init' && rawResponse.session_id) {
                    sessionId.value = rawResponse.session_id;
                }

                // Filter system messages if needed
                if (hideSystemMessages.value && rawResponse) {
                    const hasTextContent =
                        (rawResponse.type === 'content' && rawResponse.content) ||
                        (rawResponse.type === 'assistant' && rawResponse.message?.content?.some((item: any) => item.type === 'text'));

                    if (!hasTextContent && rawResponse.type !== 'content') return;
                }

                const assistantMessage = addAssistantMessage();
                appendToMessage(assistantMessage.id, text, rawResponse);
                scrollToBottom(false); // Smart scroll during streaming
            },
        );

        // Handle result
        if (result?.conversationId && !conversationId.value) {
            conversationId.value = result.conversationId;
            setTimeout(() => fetchConversations(), REFRESH_DELAY_MS);
        }
        if (result?.sessionFilename && !sessionFilename.value) {
            sessionFilename.value = result.sessionFilename;
        }
    } catch (error) {
        console.error('Error sending message:', error);
        const errorMessage = addAssistantMessage();
        appendToMessage(errorMessage.id, 'Sorry, I encountered an error. Please try again.');
    } finally {
        isLoading.value = false;
        await scrollToBottom(false); // Smart scroll after message completes

        // Handle redirects and refresh
        if (!props.sessionFile && !props.conversationId && conversationId.value) {
            router.visit(`/claude/conversation/${conversationId.value}`);
        }

        if (!props.sessionFile) {
            setTimeout(() => refreshSessions(), SESSION_REFRESH_DELAY_MS);
        }
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

// Watchers
watch(() => props.sessionFile, async (newFile, oldFile) => {
    if (newFile !== oldFile) {
        stopPolling();
        incompleteMessageFound.value = false;
        messages.value = [];

        if (newFile) {
            await loadSessionMessages();
        } else {
            sessionFilename.value = null;
            sessionId.value = null;
        }
    }
});

watch(() => props.repository, (newRepo) => {
    selectedRepository.value = newRepo || null;
}, { immediate: true });

// Lifecycle
onMounted(async () => {
    console.log('Claude.vue mounted with props:', {
        sessionFile: props.sessionFile,
        conversationId: props.conversationId,
        sessionId: props.sessionId,
        repository: props.repository
    });

    await fetchRepositories();

    if (props.conversationId) conversationId.value = props.conversationId;
    if (props.sessionId) sessionId.value = props.sessionId;

    if (props.sessionFile) {
        console.log('Loading session file:', props.sessionFile);
        await loadSessionMessages();
        // If no messages were loaded from session file, also check database
        if (messages.value.length === 0 && props.conversationId) {
            console.log('No messages from session file, loading from database');
            await loadConversationMessages();
        }
    } else if (props.conversationId) {
        // Load messages from database (for job-based conversations from quick chat)
        await loadConversationMessages();
        // Start polling immediately since message might still be processing
        if (!pollingInterval.value) {
            startPolling(POLLING_INTERVAL_MS);
        }
    } else {
        messages.value = [];
        sessionFilename.value = null;
        sessionId.value = null;
        focusInput(false);
    }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header-actions>
            <Button
                @click="hideSystemMessages = !hideSystemMessages"
                variant="ghost"
                size="icon"
                :title="hideSystemMessages ? 'Show System Messages' : 'Hide System Messages'"
                class="mr-2"
            >
                <component :is="hideSystemMessages ? EyeOff : Eye" class="h-4 w-4" />
            </Button>
        </template>
        <div class="flex h-[calc(100vh-4rem)] flex-col bg-gray-50 dark:bg-gray-900">
            <!-- Chat Messages -->
            <ScrollArea ref="messagesContainer" class="flex-1 p-4">
                <div class="space-y-4">
                    <ChatMessage
                        v-for="message in filteredMessages"
                        :key="message.id"
                        :message="message"
                        :format-time="formatTime"
                        :show-raw-responses="false"
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
                <div>
                    <div v-if="selectedRepository" class="mb-2 flex items-center text-sm text-muted-foreground">
                        <GitBranch class="mr-1 h-3 w-3" />
                        Working in:
                        <span class="ml-1 font-medium">{{ selectedRepositoryData ? selectedRepositoryData.name : selectedRepository }}</span>
                        <span v-if="!selectedRepositoryData && repositories.length > 0" class="ml-2 text-xs text-yellow-600">(not found)</span>
                    </div>
                    <div class="flex items-end space-x-2">
                        <Textarea
                            ref="textareaRef"
                            v-model="inputMessage"
                            @keydown="handleKeydown"
                            @input="adjustTextareaHeight"
                            placeholder="Type a message..."
                            class="max-h-[120px] min-h-[40px] resize-none overflow-y-auto text-sm"
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

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
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
const sessionFilename = ref<string | null>(null);
const sessionId = ref<string | null>(null);
const rawJsonResponses = ref<any[]>([]);

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const adjustTextareaHeight = () => {
    nextTick(() => {
        const textareaComponent = textareaRef.value;
        if (textareaComponent) {
            const textarea = textareaComponent.$el as HTMLTextAreaElement;
            if (textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
            }
        }
    });
};

const initializeSessionFile = () => {
    if (!sessionFilename.value && sessionId.value) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').substring(0, 19);
        sessionFilename.value = `claude-session-${timestamp}-${sessionId.value}.json`;
    }
};

const saveResponseToFile = async (userMessage: string, isComplete: boolean = false) => {
    try {
        if (!sessionId.value || !sessionFilename.value) {
            console.warn('Cannot save: missing sessionId or filename', { sessionId: sessionId.value, filename: sessionFilename.value });
            return;
        }
        
        const messageData = {
            sessionId: sessionId.value,
            userMessage: userMessage,
            timestamp: new Date().toISOString(),
            isComplete: isComplete,
            rawJsonResponses: rawJsonResponses.value
        };
        
        console.log('Saving to file:', sessionFilename.value, 'with', rawJsonResponses.value.length, 'responses');
        
        const response = await fetch('/api/claude/save-response', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ 
                filename: sessionFilename.value, 
                message: messageData 
            }),
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Save response error:', response.status, errorText);
        }
    } catch (error) {
        console.error('Error saving response to file:', error);
    }
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
        const textareaComponent = textareaRef.value;
        if (textareaComponent) {
            const textarea = textareaComponent.$el as HTMLTextAreaElement;
            if (textarea) {
                textarea.style.height = 'auto';
            }
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

    // Reset raw JSON responses for new conversation
    rawJsonResponses.value = [];

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
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            
            // Process complete JSON lines
            const lines = buffer.split('\n');
            buffer = lines.pop() || ''; // Keep incomplete line in buffer
            
            for (const line of lines) {
                if (line.trim()) {
                    try {
                        const jsonData = JSON.parse(line);
                        console.log('Received JSON:', jsonData);
                        rawJsonResponses.value.push(jsonData);
                        
                        // Extract session ID from the first response
                        if (!sessionId.value) {
                            // Try different possible fields for session ID
                            if (jsonData.sessionId) {
                                sessionId.value = jsonData.sessionId;
                            } else if (jsonData.session_id) {
                                sessionId.value = jsonData.session_id;
                            } else if (jsonData.id) {
                                sessionId.value = jsonData.id;
                            } else if (jsonData.conversationId) {
                                sessionId.value = jsonData.conversationId;
                            } else {
                                // If no session ID found, generate one
                                sessionId.value = 'generated-' + Date.now().toString(36);
                            }
                            console.log('Session ID set to:', sessionId.value);
                            initializeSessionFile();
                            console.log('Session filename:', sessionFilename.value);
                        }
                        
                        // Handle different types of JSON responses
                        if (jsonData.type === 'text' && jsonData.text) {
                            assistantMessage.content += jsonData.text;
                        } else if (jsonData.content) {
                            assistantMessage.content += jsonData.content;
                        } else if (jsonData.error) {
                            assistantMessage.content += `Error: ${jsonData.error}`;
                        }
                        
                        await scrollToBottom();
                        
                        // Save immediately after each JSON response
                        await saveResponseToFile(messageToSend, false);
                    } catch (e) {
                        console.error('Error parsing JSON:', e, 'Line:', line);
                    }
                }
            }
        }
        
        // Process any remaining buffer
        if (buffer.trim()) {
            try {
                const jsonData = JSON.parse(buffer);
                rawJsonResponses.value.push(jsonData);
                
                if (!sessionId.value && jsonData.sessionId) {
                    sessionId.value = jsonData.sessionId;
                    initializeSessionFile();
                }
                
                if (jsonData.type === 'text' && jsonData.text) {
                    assistantMessage.content += jsonData.text;
                } else if (jsonData.content) {
                    assistantMessage.content += jsonData.content;
                }
                
                // Save the final piece
                await saveResponseToFile(messageToSend, false);
            } catch (e) {
                console.error('Error parsing final buffer:', e);
            }
        }
        
        // Final save when streaming is complete
        await saveResponseToFile(messageToSend, true);
    } catch (error) {
        console.error('Error sending message:', error);
        assistantMessage.content = 'Sorry, I encountered an error. Please try again.';
    } finally {
        isLoading.value = false;
        await scrollToBottom();
        
        // Focus back to the input after sending message
        focusInput();
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

const focusInput = () => {
    nextTick(() => {
        // Access the textarea element directly from the component's exposed element
        const textareaComponent = textareaRef.value;
        if (textareaComponent && !isLoading.value) {
            // The Textarea component from shadcn/ui exposes the native element via $el
            const textarea = textareaComponent.$el as HTMLTextAreaElement;
            if (textarea) {
                textarea.focus();
            }
        }
    });
};

const handlePageClick = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    // Don't refocus if clicking on the textarea itself or interactive elements
    if (!target.closest('textarea, button, a, [role="button"]')) {
        focusInput();
    }
};

const handleVisibilityChange = () => {
    if (!document.hidden) {
        focusInput();
    }
};

onMounted(() => {
    focusInput();
    
    // Keep input focused when clicking anywhere on the page
    document.addEventListener('click', handlePageClick);
    
    // Refocus when window/tab regains focus
    window.addEventListener('focus', focusInput);
    
    // Refocus on visibility change (switching tabs)
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    document.removeEventListener('click', handlePageClick);
    window.removeEventListener('focus', focusInput);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
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
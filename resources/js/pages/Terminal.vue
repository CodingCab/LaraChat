<template>
    <Head title="Terminal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="bg-black rounded-lg p-4 font-mono text-sm">
                            <div ref="terminalContent" class="min-h-[400px] max-h-[600px] overflow-y-auto">
                                <div v-for="(entry, index) in terminalHistory" :key="index" class="mb-2">
                                    <div v-if="entry.type === 'command'" class="text-green-400">
                                        $ {{ entry.content }}
                                    </div>
                                    <div v-else-if="entry.type === 'error'" class="text-red-400 whitespace-pre-wrap">
                                        {{ entry.content }}
                                    </div>
                                    <div v-else class="text-gray-300 whitespace-pre-wrap">
                                        {{ entry.content }}
                                        <span v-if="entry.streaming" class="inline-block animate-pulse">▊</span>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-400 mr-2">$</span>
                                    <input
                                        v-model="currentCommand"
                                        @keyup.enter="executeCommand"
                                        type="text"
                                        class="flex-1 bg-transparent border-none outline-none text-gray-300 focus:ring-0"
                                        placeholder="Enter command..."
                                        ref="commandInput"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Terminal',
        href: '/terminal',
    },
];

interface TerminalEntry {
    type: 'command' | 'output' | 'error';
    content: string;
    streaming?: boolean;
}

const terminalHistory = ref<TerminalEntry[]>([]);
const currentCommand = ref('');
const commandInput = ref<HTMLInputElement | null>(null);
const terminalContent = ref<HTMLDivElement | null>(null);

const executeCommand = async () => {
    const command = currentCommand.value.trim();
    if (!command) return;

    // Add command to history
    terminalHistory.value.push({
        type: 'command',
        content: command
    });

    currentCommand.value = '';

    // Add placeholder for streaming output
    const outputIndex = terminalHistory.value.length;
    terminalHistory.value.push({
        type: 'output',
        content: '',
        streaming: true
    });

    try {
        const response = await fetch('/api/claude', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/plain',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ command })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const reader = response.body!.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();

            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';

            for (const line of lines) {
                if (line.trim()) {
                    try {
                        const data = JSON.parse(line);

                        if (data.type === 'stdout' || data.type === 'stderr') {
                            terminalHistory.value[outputIndex].content += data.data;
                            if (data.type === 'stderr') {
                                terminalHistory.value[outputIndex].type = 'error';
                            }
                        } else if (data.type === 'complete') {
                            terminalHistory.value[outputIndex].streaming = false;
                            if (!terminalHistory.value[outputIndex].content.trim()) {
                                terminalHistory.value[outputIndex].content = 'Command completed with no output';
                            }
                        } else if (data.type === 'error') {
                            terminalHistory.value[outputIndex].content += data.data + '\n';
                            terminalHistory.value[outputIndex].type = 'error';
                            terminalHistory.value[outputIndex].streaming = false;
                        }

                        await nextTick();
                        scrollToBottom();
                    } catch (e) {
                        console.error('Error parsing line:', line, e);
                    }
                }
            }
        }

        // Ensure streaming is marked as complete
        terminalHistory.value[outputIndex].streaming = false;
    } catch (error: any) {
        console.error('Command execution error:', error);
        terminalHistory.value[outputIndex].content = error.message || 'Error executing command';
        terminalHistory.value[outputIndex].streaming = false;
        terminalHistory.value[outputIndex].type = 'error';
    }

    await nextTick();
    scrollToBottom();
};

const scrollToBottom = () => {
    if (terminalContent.value) {
        terminalContent.value.scrollTop = terminalContent.value.scrollHeight;
    }
};

onMounted(() => {
    commandInput.value?.focus();
});
</script>

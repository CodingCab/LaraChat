<template>
    <Head title="Terminal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="bg-black rounded-lg p-4 font-mono text-sm">
                            <div class="min-h-[400px] max-h-[600px] overflow-y-auto">
                                <div v-for="(entry, index) in terminalHistory" :key="index" class="mb-2">
                                    <div v-if="entry.type === 'command'" class="text-green-400">
                                        $ {{ entry.content }}
                                    </div>
                                    <div v-else class="text-gray-300 whitespace-pre-wrap">{{ entry.content }}</div>
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
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Terminal',
        href: '/terminal',
    },
];

const terminalHistory = ref([]);
const currentCommand = ref('');
const commandInput = ref(null);

const executeCommand = async () => {
    const command = currentCommand.value.trim();
    if (!command) return;

    terminalHistory.value.push({
        type: 'command',
        content: command
    });

    currentCommand.value = '';

    try {
        const response = await axios.post('/api/command/execute', {
            command: command
        });

        terminalHistory.value.push({
            type: 'output',
            content: response.data.output || 'Command executed successfully'
        });
    } catch (error) {
        terminalHistory.value.push({
            type: 'output',
            content: error.response?.data?.message || 'Error executing command'
        });
    }

    await nextTick();
    scrollToBottom();
};

const scrollToBottom = () => {
    const terminal = document.querySelector('.overflow-y-auto');
    if (terminal) {
        terminal.scrollTop = terminal.scrollHeight;
    }
};

onMounted(() => {
    commandInput.value?.focus();
});
</script>
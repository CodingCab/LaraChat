<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const command = ref('');
const commandResult = ref('');
const commandSuccess = ref(false);
const isRunning = ref(false);

const runCommand = async () => {
    if (!command.value.trim()) {
        commandResult.value = 'Please enter a command';
        commandSuccess.value = false;
        return;
    }

    isRunning.value = true;
    commandResult.value = 'Running command...';
    commandSuccess.value = false;

    try {
        const response = await axios.post(
            '/api/run-command',
            {
                command: command.value.trim(),
            },
            {
                timeout: 600000, // 10 minutes timeout for long-running commands
            },
        );
        commandResult.value = response.data.output;
        commandSuccess.value = response.data.success;
    } catch (error: any) {
        console.error('Command error:', error);
        commandSuccess.value = false;
        if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
            commandResult.value =
                'Command timed out. The command is taking longer than expected. For long-running commands like git clone, try running them in smaller steps or check if the process is still running on the server.';
        } else if (error.response?.status === 504) {
            commandResult.value =
                'Gateway timeout: The server took too long to respond. This usually happens with long-running commands. The command might still be running on the server.';
        } else if (error.response?.data?.output) {
            commandResult.value = error.response.data.output;
        } else if (error.response?.data?.message) {
            commandResult.value = error.response.data.message;
        } else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat().join(', ');
            commandResult.value = `Validation Error: ${errors}`;
        } else {
            commandResult.value = `Error running command: ${error.message || 'Unknown error'}`;
        }
    } finally {
        isRunning.value = false;
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mb-4 flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Run Command</label>
                    <div class="flex items-center gap-4">
                        <Input
                            v-model="command"
                            placeholder="Enter command to run (e.g., pwd, ls -la, git clone https://github.com/user/repo.git)"
                            class="flex-1 font-mono"
                            :disabled="isRunning"
                            @keyup.enter="runCommand"
                        />
                        <Button @click="runCommand" :disabled="isRunning || !command.trim()">
                            {{ isRunning ? 'Running...' : 'Run Command' }}
                        </Button>
                    </div>
                    <div
                        v-if="commandResult"
                        :class="[
                            'rounded-md p-4 font-mono text-sm transition-all',
                            commandSuccess
                                ? 'border border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400'
                                : 'border border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400',
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <div v-if="!commandSuccess" class="mt-0.5">
                                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <div v-else class="mt-0.5">
                                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="mb-2 font-medium">{{ commandSuccess ? 'Command executed successfully' : 'Command failed' }}</p>
                                <pre class="overflow-x-auto break-words whitespace-pre-wrap">{{ commandResult }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>

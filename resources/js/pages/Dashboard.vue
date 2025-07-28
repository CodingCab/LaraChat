<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const pwdResult = ref('');
const isLoading = ref(false);

const runPwdCommand = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('/api/run-command', { command: 'pwd' });
        pwdResult.value = response.data.output;
    } catch (error) {
        pwdResult.value = 'Error running command';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex items-center gap-4 mb-4">
                <Button @click="runPwdCommand" :disabled="isLoading">
                    {{ isLoading ? 'Running...' : 'Run PWD Command' }}
                </Button>
                <div v-if="pwdResult" class="flex-1 p-3 bg-muted rounded-md font-mono text-sm">
                    {{ pwdResult }}
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

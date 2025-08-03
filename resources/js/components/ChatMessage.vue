<script setup lang="ts">
import type { Message } from '@/types/claude';

defineProps<{
    message: Message;
    formatTime: (date: Date) => string;
    showRawResponses: boolean;
}>();
</script>

<template>
    <div :class="['flex', message.role === 'user' ? 'justify-end' : 'justify-start']">
        <div
            :class="[
                'max-w-[70%] rounded-2xl px-4 py-2 shadow-sm',
                message.role === 'user' ? 'bg-green-500 text-white' : 'bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100',
            ]"
        >
            <p class="break-words whitespace-pre-wrap text-sm">{{ message.content }}</p>
            <p :class="['mt-1 text-[11px]', message.role === 'user' ? 'text-green-100' : 'text-gray-500 dark:text-gray-400']">
                {{ formatTime(message.timestamp) }}
            </p>
            <!-- Raw JSON Responses Display -->
            <div
                v-if="showRawResponses && message.role === 'assistant' && message.rawResponses && message.rawResponses.length > 0"
                class="mt-2 space-y-2"
            >
                <div class="text-[11px] text-gray-500 dark:text-gray-400">Raw JSON Responses ({{ message.rawResponses.length }}):</div>
                <div
                    v-for="(response, index) in message.rawResponses"
                    :key="`raw-${message.id}-${index}`"
                    class="overflow-x-auto rounded bg-gray-100 p-2 font-mono text-[11px] dark:bg-gray-800"
                >
                    <pre>{{ JSON.stringify(response, null, 2) }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>

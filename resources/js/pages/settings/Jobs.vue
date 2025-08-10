<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import Alert from '@/components/ui/alert.vue';
import AlertDescription from '@/components/ui/alert-description.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { 
    Loader2, 
    AlertCircle, 
    CheckCircle2, 
    Play, 
    Square, 
    RefreshCw,
    Activity,
    Clock
} from 'lucide-vue-next';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Jobs',
        href: '/settings/jobs',
    },
];

interface WorkerStatus {
    id: string;
    pid: number;
    status: 'running' | 'stopped' | 'idle';
    startTime: string;
    processedJobs: number;
    failedJobs: number;
    memory: string;
    cpu?: string;
}

interface QueueStats {
    pending: number;
    running: number;
    failed: number;
}

const workers = ref<WorkerStatus[]>([]);
const queueStats = ref<QueueStats>({ pending: 0, running: 0, failed: 0 });
const isLoading = ref(false);
const isStarting = ref(false);
const isStopping = ref(false);
const statusMessage = ref('');
const statusType = ref<'success' | 'error' | 'info'>('info');
const refreshInterval = ref<NodeJS.Timeout | null>(null);

const form = useForm({
    action: '',
    workerId: '',
});

const fetchWorkerStatus = async () => {
    try {
        const response = await fetch(route('settings.jobs.status'));
        const data = await response.json();
        workers.value = data.workers || [];
        queueStats.value = data.stats || { pending: 0, running: 0, failed: 0 };
        
        // If no workers in cache but processes are running, use those
        if (workers.value.length === 0 && data.processes && data.processes.length > 0) {
            workers.value = data.processes.map((p: any) => ({
                id: `process_${p.pid}`,
                pid: p.pid,
                status: 'running',
                startTime: new Date().toISOString(),
                processedJobs: 0,
                failedJobs: 0,
                memory: p.memory || 'N/A',
                cpu: p.cpu || '0',
            }));
        }
    } catch (error) {
        console.error('Failed to fetch worker status:', error);
    }
};

const startWorker = () => {
    isStarting.value = true;
    statusMessage.value = '';
    
    form.action = 'start';
    form.post(route('settings.jobs.control'), {
        preserveScroll: true,
        onSuccess: (page: any) => {
            isStarting.value = false;
            statusType.value = 'success';
            statusMessage.value = page.props.flash?.message || 'Queue worker started successfully!';
            fetchWorkerStatus();
        },
        onError: (errors: any) => {
            isStarting.value = false;
            statusType.value = 'error';
            statusMessage.value = errors.message || 'Failed to start queue worker.';
        },
    });
};

const stopWorker = (workerId?: string) => {
    isStopping.value = true;
    statusMessage.value = '';
    
    form.action = 'stop';
    form.workerId = workerId || '';
    form.post(route('settings.jobs.control'), {
        preserveScroll: true,
        onSuccess: (page: any) => {
            isStopping.value = false;
            statusType.value = 'success';
            statusMessage.value = page.props.flash?.message || 'Queue worker stopped successfully!';
            fetchWorkerStatus();
        },
        onError: (errors: any) => {
            isStopping.value = false;
            statusType.value = 'error';
            statusMessage.value = errors.message || 'Failed to stop queue worker.';
        },
    });
};

const refreshStatus = () => {
    isLoading.value = true;
    fetchWorkerStatus().finally(() => {
        isLoading.value = false;
    });
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'running': return 'text-green-600 dark:text-green-400';
        case 'idle': return 'text-yellow-600 dark:text-yellow-400';
        case 'stopped': return 'text-gray-600 dark:text-gray-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};

const getStatusIcon = (status: string) => {
    switch(status) {
        case 'running': return Activity;
        case 'idle': return Clock;
        case 'stopped': return Square;
        default: return AlertCircle;
    }
};

onMounted(() => {
    fetchWorkerStatus();
    refreshInterval.value = setInterval(fetchWorkerStatus, 5000);
});

onUnmounted(() => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Jobs" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall 
                    title="Queue Workers" 
                    description="Manage and monitor queue workers for background job processing" 
                />

                <div class="space-y-4">
                    <Alert>
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription>
                            Queue workers process background jobs such as sending emails, processing uploads, and other asynchronous tasks.
                            The worker will run with a timeout of 3600 seconds (1 hour).
                        </AlertDescription>
                    </Alert>

                    <div class="flex items-center gap-4">
                        <Button 
                            @click="startWorker" 
                            :disabled="isStarting || isStopping"
                            variant="default"
                        >
                            <Play v-if="!isStarting" class="mr-2 h-4 w-4" />
                            <Loader2 v-if="isStarting" class="mr-2 h-4 w-4 animate-spin" />
                            {{ isStarting ? 'Starting...' : 'Start Queue Worker' }}
                        </Button>

                        <Button 
                            @click="refreshStatus" 
                            :disabled="isLoading"
                            variant="outline"
                        >
                            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
                            <span class="ml-2">Refresh</span>
                        </Button>
                    </div>

                    <Alert v-if="statusMessage" :class="{
                        'border-green-500 bg-green-50 dark:bg-green-950/20': statusType === 'success',
                        'border-red-500 bg-red-50 dark:bg-red-950/20': statusType === 'error',
                        'border-blue-500 bg-blue-50 dark:bg-blue-950/20': statusType === 'info'
                    }">
                        <CheckCircle2 v-if="statusType === 'success'" class="h-4 w-4 text-green-600" />
                        <AlertCircle v-if="statusType === 'error'" class="h-4 w-4 text-red-600" />
                        <AlertCircle v-if="statusType === 'info'" class="h-4 w-4 text-blue-600" />
                        <AlertDescription :class="{
                            'text-green-800 dark:text-green-300': statusType === 'success',
                            'text-red-800 dark:text-red-300': statusType === 'error',
                            'text-blue-800 dark:text-blue-300': statusType === 'info'
                        }">
                            {{ statusMessage }}
                        </AlertDescription>
                    </Alert>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Queue Statistics</h3>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="border rounded-lg p-3">
                                <div class="text-sm text-muted-foreground">Pending Jobs</div>
                                <div class="text-2xl font-semibold">{{ queueStats.pending }}</div>
                            </div>
                            <div class="border rounded-lg p-3">
                                <div class="text-sm text-muted-foreground">Running Jobs</div>
                                <div class="text-2xl font-semibold text-blue-600 dark:text-blue-400">{{ queueStats.running }}</div>
                            </div>
                            <div class="border rounded-lg p-3">
                                <div class="text-sm text-muted-foreground">Failed Jobs</div>
                                <div class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ queueStats.failed }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Active Workers</h3>
                        
                        <div v-if="workers.length === 0" class="text-muted-foreground">
                            No active workers found. Click "Start Queue Worker" to begin processing jobs.
                        </div>

                        <div v-else class="space-y-3">
                            <div 
                                v-for="worker in workers" 
                                :key="worker.id"
                                class="border rounded-lg p-4 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <component 
                                            :is="getStatusIcon(worker.status)" 
                                            :class="['h-5 w-5', getStatusColor(worker.status)]"
                                        />
                                        <span class="font-medium">Worker PID: {{ worker.pid }}</span>
                                        <span :class="['text-sm', getStatusColor(worker.status)]">
                                            ({{ worker.status }})
                                        </span>
                                    </div>
                                    <Button 
                                        @click="stopWorker(worker.id)" 
                                        :disabled="isStopping"
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Square class="h-3 w-3 mr-1" />
                                        Stop
                                    </Button>
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                    <div>
                                        <span class="text-muted-foreground">Started:</span>
                                        <div class="font-mono text-xs">{{ new Date(worker.startTime).toLocaleTimeString() }}</div>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Processed:</span>
                                        <div class="font-mono">{{ worker.processedJobs }}</div>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Failed:</span>
                                        <div class="font-mono text-red-600 dark:text-red-400">
                                            {{ worker.failedJobs }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Memory:</span>
                                        <div class="font-mono">{{ worker.memory }}</div>
                                    </div>
                                    <div v-if="worker.cpu">
                                        <span class="text-muted-foreground">CPU:</span>
                                        <div class="font-mono">{{ worker.cpu }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
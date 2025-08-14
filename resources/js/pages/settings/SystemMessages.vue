<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { type BreadcrumbItem } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Trash2, Edit, Plus, Check, X } from 'lucide-vue-next';

interface SystemMessage {
    id: number;
    name: string | null;
    message: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

defineProps<{
    systemMessages: SystemMessage[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings' },
    { title: 'System Messages', href: '/settings/system-messages' },
];

const isCreating = ref(false);
const editingId = ref<number | null>(null);

const createForm = useForm({
    name: '',
    message: '',
    is_active: false,
});

const editForm = useForm({
    name: '',
    message: '',
    is_active: false,
});

const startCreating = () => {
    isCreating.value = true;
    createForm.reset();
};

const cancelCreating = () => {
    isCreating.value = false;
    createForm.reset();
};

const createSystemMessage = () => {
    createForm.post('/settings/system-messages', {
        onSuccess: () => {
            isCreating.value = false;
            createForm.reset();
        },
    });
};

const startEditing = (message: SystemMessage) => {
    editingId.value = message.id;
    editForm.name = message.name || '';
    editForm.message = message.message;
    editForm.is_active = message.is_active;
};

const cancelEditing = () => {
    editingId.value = null;
    editForm.reset();
};

const updateSystemMessage = (id: number) => {
    editForm.put(`/settings/system-messages/${id}`, {
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

const deleteSystemMessage = (id: number) => {
    if (confirm('Are you sure you want to delete this system message?')) {
        useForm({}).delete(`/settings/system-messages/${id}`);
    }
};

const toggleSystemMessage = (id: number) => {
    useForm({}).post(`/settings/system-messages/${id}/toggle`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>System Messages</CardTitle>
                            <CardDescription>
                                Configure system messages that will be appended to every conversation with Claude.
                                Only one message can be active at a time.
                            </CardDescription>
                        </div>
                        <Button 
                            v-if="!isCreating"
                            @click="startCreating"
                            size="sm"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Add Message
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Create new message form -->
                    <div v-if="isCreating" class="mb-6 p-4 border rounded-lg bg-muted/50">
                        <form @submit.prevent="createSystemMessage" class="space-y-4">
                            <div>
                                <Label for="new-name">Name (optional)</Label>
                                <Input
                                    id="new-name"
                                    v-model="createForm.name"
                                    placeholder="e.g., Development Context"
                                    :disabled="createForm.processing"
                                />
                            </div>
                            <div>
                                <Label for="new-message">Message</Label>
                                <Textarea
                                    id="new-message"
                                    v-model="createForm.message"
                                    placeholder="Enter the system message that will be appended to conversations..."
                                    rows="4"
                                    required
                                    :disabled="createForm.processing"
                                />
                            </div>
                            <div class="flex items-center space-x-2">
                                <Switch
                                    id="new-active"
                                    v-model:checked="createForm.is_active"
                                    :disabled="createForm.processing"
                                />
                                <Label for="new-active">Set as active</Label>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <Button 
                                    type="button"
                                    variant="outline" 
                                    size="sm"
                                    @click="cancelCreating"
                                    :disabled="createForm.processing"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Cancel
                                </Button>
                                <Button 
                                    type="submit" 
                                    size="sm"
                                    :disabled="createForm.processing || !createForm.message"
                                >
                                    <Check class="mr-2 h-4 w-4" />
                                    Create
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- List of system messages -->
                    <div v-if="systemMessages.length > 0" class="space-y-4">
                        <div 
                            v-for="message in systemMessages" 
                            :key="message.id"
                            class="p-4 border rounded-lg"
                            :class="{ 'bg-primary/5 border-primary': message.is_active }"
                        >
                            <div v-if="editingId === message.id">
                                <form @submit.prevent="updateSystemMessage(message.id)" class="space-y-4">
                                    <div>
                                        <Label :for="`edit-name-${message.id}`">Name (optional)</Label>
                                        <Input
                                            :id="`edit-name-${message.id}`"
                                            v-model="editForm.name"
                                            placeholder="e.g., Development Context"
                                            :disabled="editForm.processing"
                                        />
                                    </div>
                                    <div>
                                        <Label :for="`edit-message-${message.id}`">Message</Label>
                                        <Textarea
                                            :id="`edit-message-${message.id}`"
                                            v-model="editForm.message"
                                            rows="4"
                                            required
                                            :disabled="editForm.processing"
                                        />
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Switch
                                            :id="`edit-active-${message.id}`"
                                            v-model:checked="editForm.is_active"
                                            :disabled="editForm.processing"
                                        />
                                        <Label :for="`edit-active-${message.id}`">Set as active</Label>
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <Button 
                                            type="button"
                                            variant="outline" 
                                            size="sm"
                                            @click="cancelEditing"
                                            :disabled="editForm.processing"
                                        >
                                            <X class="mr-2 h-4 w-4" />
                                            Cancel
                                        </Button>
                                        <Button 
                                            type="submit" 
                                            size="sm"
                                            :disabled="editForm.processing || !editForm.message"
                                        >
                                            <Check class="mr-2 h-4 w-4" />
                                            Save
                                        </Button>
                                    </div>
                                </form>
                            </div>
                            <div v-else>
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="font-medium">
                                                {{ message.name || 'Unnamed Message' }}
                                            </h3>
                                            <span 
                                                v-if="message.is_active" 
                                                class="px-2 py-0.5 text-xs font-medium bg-primary text-primary-foreground rounded"
                                            >
                                                Active
                                            </span>
                                        </div>
                                        <p class="text-sm text-muted-foreground whitespace-pre-wrap">
                                            {{ message.message }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4">
                                        <Switch
                                            :checked="message.is_active"
                                            @update:checked="toggleSystemMessage(message.id)"
                                            :title="message.is_active ? 'Deactivate' : 'Activate'"
                                        />
                                        <Button
                                            @click="startEditing(message)"
                                            variant="ghost"
                                            size="icon"
                                            title="Edit"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            @click="deleteSystemMessage(message.id)"
                                            variant="ghost"
                                            size="icon"
                                            title="Delete"
                                            class="text-destructive hover:text-destructive"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else-if="!isCreating" class="text-center py-8 text-muted-foreground">
                        <p>No system messages configured yet.</p>
                        <p class="text-sm mt-2">Create your first system message to enhance your Claude conversations.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
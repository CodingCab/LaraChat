import axios from 'axios';
import { computed, onUnmounted, ref } from 'vue';

interface Conversation {
    id: number;
    user_id: number;
    title: string;
    repository: string | null;
    project_directory: string | null;
    claude_session_id: string | null;
    is_processing: boolean;
    created_at: string;
    updated_at: string;
}

const conversations = ref<Conversation[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
let refreshInterval: number | null = null;

export function useConversations() {
    const fetchConversations = async (silent = false) => {
        if (!silent) {
            loading.value = true;
        }
        error.value = null;

        try {
            const response = await axios.get<Conversation[]>('/api/claude/conversations');
            conversations.value = response.data;
            
            // Check if any conversations are processing
            const hasProcessing = response.data.some(conv => conv.is_processing);
            
            // Set up or clear interval based on processing status
            if (hasProcessing && !refreshInterval) {
                // Start periodic refresh every 2 seconds
                refreshInterval = window.setInterval(() => {
                    fetchConversations(true); // Silent refresh
                }, 2000);
            } else if (!hasProcessing && refreshInterval) {
                // Clear interval if no conversations are processing
                window.clearInterval(refreshInterval);
                refreshInterval = null;
            }
        } catch (err) {
            error.value = 'Failed to fetch conversations';
            console.error('Error fetching conversations:', err);
        } finally {
            if (!silent) {
                loading.value = false;
            }
        }
    };

    // Check if any conversation is processing
    const hasProcessingConversations = computed(() => {
        return conversations.value.some(conv => conv.is_processing);
    });

    // Clean up interval on component unmount
    const cleanup = () => {
        if (refreshInterval) {
            window.clearInterval(refreshInterval);
            refreshInterval = null;
        }
    };

    // Ensure cleanup when composable is no longer used
    onUnmounted(() => {
        cleanup();
    });

    return {
        conversations,
        loading,
        error,
        fetchConversations,
        hasProcessingConversations,
        cleanup,
    };
}

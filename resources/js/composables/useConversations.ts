import axios from 'axios';
import { ref } from 'vue';

interface Conversation {
    id: number;
    user_id: number;
    title: string;
    repository: string | null;
    project_directory: string | null;
    claude_session_id: string | null;
    created_at: string;
    updated_at: string;
}

const conversations = ref<Conversation[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

export function useConversations() {
    const fetchConversations = async () => {
        loading.value = true;
        error.value = null;

        try {
            console.log('Fetching conversations...');
            const response = await axios.get<Conversation[]>('/api/claude/conversations');
            console.log('Conversations received:', response.data);
            conversations.value = response.data;
        } catch (err) {
            error.value = 'Failed to fetch conversations';
            console.error('Error fetching conversations:', err);
        } finally {
            loading.value = false;
        }
    };

    return {
        conversations,
        loading,
        error,
        fetchConversations,
    };
}

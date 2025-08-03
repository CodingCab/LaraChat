import type { ClaudeApiRequest, SessionConversation } from '@/types/claude';
import axios from 'axios';
import { ref } from 'vue';

export function useClaudeApi() {
    const isLoading = ref(false);

    const sendMessageToApi = async (request: ClaudeApiRequest, onChunk: (text: string, rawResponse: any) => void): Promise<void> => {
        const response = await fetch('/api/claude', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(request),
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
                        
                        // Always pass the raw response to onChunk for proper handling
                        onChunk('', jsonData);
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
                
                // Always pass the raw response to onChunk for proper handling
                onChunk('', jsonData);
            } catch (e) {
                console.error('Error parsing final buffer:', e);
            }
        }
    };

    const loadSession = async (sessionFile: string): Promise<SessionConversation[]> => {
        const response = await axios.get(`/api/claude/sessions/${sessionFile}`);
        return response.data;
    };

    return {
        isLoading,
        sendMessageToApi,
        loadSession,
    };
}

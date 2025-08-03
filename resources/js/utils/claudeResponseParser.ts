interface ClaudeResponse {
    type?: string;
    content?: string | { type: string; text: string };
    text?: string;
    error?: string;
}

export function parseClaudeResponse(jsonData: ClaudeResponse): string {
    // Handle error responses
    if (jsonData.error) {
        return `Error: ${jsonData.error}`;
    }

    // Handle different types of JSON responses from Claude CLI
    if (jsonData.type === 'content' && jsonData.content) {
        if (typeof jsonData.content === 'object' && jsonData.content.type === 'text' && jsonData.content.text) {
            // Claude CLI format: {"type":"content","content":{"type":"text","text":"..."}}
            return jsonData.content.text;
        } else if (typeof jsonData.content === 'string') {
            return jsonData.content;
        }
    } else if (jsonData.type === 'text' && jsonData.text) {
        return jsonData.text;
    } else if (jsonData.text && typeof jsonData.text === 'string') {
        return jsonData.text;
    } else if (jsonData.content && typeof jsonData.content === 'string') {
        return jsonData.content;
    }

    return '';
}

export function extractTextFromResponses(rawResponses: ClaudeResponse[]): string {
    let content = '';

    for (const response of rawResponses) {
        if (!response.error) {
            content += parseClaudeResponse(response);
        }
    }

    return content;
}

export function extractTextFromResponse(rawResponse: any): string {
    // Handle Claude Code CLI response format
    if (rawResponse.type === 'assistant' && rawResponse.message) {
        const message = rawResponse.message;
        if (message.content && Array.isArray(message.content)) {
            return message.content
                .filter((item: any) => item.type === 'text')
                .map((item: any) => item.text)
                .join('');
        }
    }

    // Handle other response types
    if (rawResponse.type === 'result' && rawResponse.result) {
        return rawResponse.result;
    }

    // Fallback to original parser
    return parseClaudeResponse(rawResponse);
}

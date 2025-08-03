export function parseUrlsToLinks(text: string): string {
    // Regex pattern to match URLs
    const urlPattern = /(https?:\/\/[^\s<]+[^<.,:;"')\]\s])/g;

    // Replace URLs with HTML anchor tags
    return text.replace(urlPattern, (url) => {
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-80">${url}</a>`;
    });
}

export function escapeHtml(text: string): string {
    const map: { [key: string]: string } = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

export function parseMessageContent(content: string): string {
    // First escape HTML to prevent XSS
    const escapedContent = escapeHtml(content);
    // Then convert URLs to links
    return parseUrlsToLinks(escapedContent);
}

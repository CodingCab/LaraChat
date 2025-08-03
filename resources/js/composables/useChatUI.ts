import { nextTick, onMounted, onUnmounted, ref } from 'vue';

export function useChatUI() {
    const messagesContainer = ref<HTMLElement>();
    const textareaRef = ref<HTMLTextAreaElement>();

    const scrollToBottom = async () => {
        await nextTick();
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    };

    const adjustTextareaHeight = () => {
        nextTick(() => {
            const textareaComponent = textareaRef.value;
            if (textareaComponent) {
                const textarea = textareaComponent.$el as HTMLTextAreaElement;
                if (textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
                }
            }
        });
    };

    const resetTextareaHeight = () => {
        nextTick(() => {
            const textareaComponent = textareaRef.value;
            if (textareaComponent) {
                const textarea = textareaComponent.$el as HTMLTextAreaElement;
                if (textarea) {
                    textarea.style.height = 'auto';
                }
            }
        });
    };

    const focusInput = (isLoading: boolean = false) => {
        nextTick(() => {
            const textareaComponent = textareaRef.value;
            if (textareaComponent && !isLoading) {
                const textarea = textareaComponent.$el as HTMLTextAreaElement;
                if (textarea) {
                    textarea.focus();
                }
            }
        });
    };

    const handlePageClick = (e: MouseEvent, isLoading: boolean) => {
        const target = e.target as HTMLElement;
        if (!target.closest('textarea, button, a, [role="button"]')) {
            focusInput(isLoading);
        }
    };

    const handleVisibilityChange = (isLoading: boolean) => {
        if (!document.hidden) {
            focusInput(isLoading);
        }
    };

    const setupFocusHandlers = (isLoading: { value: boolean }) => {
        const pageClickHandler = (e: MouseEvent) => handlePageClick(e, isLoading.value);
        const visibilityHandler = () => handleVisibilityChange(isLoading.value);
        const focusHandler = () => focusInput(isLoading.value);

        onMounted(() => {
            document.addEventListener('click', pageClickHandler);
            window.addEventListener('focus', focusHandler);
            document.addEventListener('visibilitychange', visibilityHandler);
        });

        onUnmounted(() => {
            document.removeEventListener('click', pageClickHandler);
            window.removeEventListener('focus', focusHandler);
            document.removeEventListener('visibilitychange', visibilityHandler);
        });
    };

    return {
        messagesContainer,
        textareaRef,
        scrollToBottom,
        adjustTextareaHeight,
        resetTextareaHeight,
        focusInput,
        setupFocusHandlers,
    };
}

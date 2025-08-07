<template>
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body" ref="loadingContainer">
                        <div v-for="(msg, index) in messages" :key="index" class="mb-2">
                            <div v-if="msg.role === 'user'" class="text-right font-weight-bold">{{ $t('You') }}: {{ msg.content }}</div>
                            <div v-else class="text-left text-primary">GPT: {{ msg.content }}</div>
                        </div>
                        <div class="input-group mt-3">
                            <input v-model="input" type="text" class="form-control" @keyup.enter="send"/>
                            <div class="input-group-append">
                                <button class="btn btn-primary" @click="send">{{ $t('Send') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../mixins/api";
import Loading from "../mixins/loading-overlay";
export default {
    mixins: [api, Loading],
    data() {
        return {
            input: '',
            messages: []
        };
    },
    methods: {
        send() {
            if (!this.input) return;
            const message = this.input;
            this.messages.push({ role: 'user', content: message });
            this.input = '';
            this.showLoading();
            this.apiPostChatGptChat({ message })
                .then(({ data }) => {
                    this.messages.push({ role: 'assistant', content: data.reply });
                })
                .catch(this.displayApiCallError)
                .finally(this.hideLoading);
        }
    }
};
</script>

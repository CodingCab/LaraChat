<template>
<div>
    <div class="alert-heartbeat" v-for="heartbeat in heartbeats" :key="heartbeat.id">
        <div class="px-3 py-1">
            {{ heartbeat.error_message }}
        </div>
    </div>
</div>
</template>

<script>
import api from "../mixins/api";

export default {
    mixins: [api],
    data(){
        return {
            heartbeats: [],
        }
    },
    mounted(){
        this.getHeartbeats();
    },
    methods: {
        getHeartbeats(){
            this.apiGetHeartbeats()
                .then(response => {
                    this.heartbeats = response.data.data
                })
                .catch(error => {
                    this.heartbeats = []
                })
        }
    }
}
</script>

<style scoped>
.alert-heartbeat{
    background-color: #e3342f;
    border-left: 5px solid #ab211d;
    color: #fff;
    margin-bottom: 1px;
}
</style>

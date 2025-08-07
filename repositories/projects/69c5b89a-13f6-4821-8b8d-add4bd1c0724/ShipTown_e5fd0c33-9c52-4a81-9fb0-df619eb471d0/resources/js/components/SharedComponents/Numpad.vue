<template>
    <div class="numpad-grid">
        <button v-for="n in [7,8,9,4,5,6,1,2,3]"
                :key="n"
                :id="'keypad' + n"
                class="btn btn-outline-secondary"
                @touchstart.stop.prevent="press(n)"
                @click.prevent="press(n)">{{ n }}</button>
        <button id="keypadBackspace" class="btn btn-outline-secondary" @touchstart.stop.prevent="$emit('backspace')" @click.prevent="$emit('backspace')">←</button>
        <button id="keypad0" class="btn btn-outline-secondary" @touchstart.stop.prevent="press(0)" @click.prevent="press(0)">0</button>
        <button id="keypadOk" class="btn btn-outline-primary" @touchstart.stop.prevent="$emit('ok')" @click.prevent="$emit('ok')">OK</button>
        <button id="keypadMinus" class="btn btn-outline-secondary" @touchstart.stop.prevent="press('-')" @click.prevent="press('-')">-</button>
        <button id="keypadDot" class="btn btn-outline-secondary" @touchstart.stop.prevent="press('.')" @click.prevent="press('.')">.</button>
        <button id="keypadCancel" class="btn btn-outline-secondary btn-sm" @touchstart.stop.prevent="$emit('cancel')" @click.prevent="$emit('cancel')">Cancel</button>
    </div>
</template>

<script>
export default {
    name: 'Numpad',
    emits: ['press', 'backspace', 'ok', 'cancel'],
    methods: {
        press(value) {
            this.$emit('press', value);
        }
    }
}
</script>

<style lang="scss" scoped>
.numpad-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 5px;
    max-width: 450px;
    margin: 0 auto;

    .btn {
        padding: 12px;
        font-size: 1.2em;
        font-weight: bold;
    }
}
</style>

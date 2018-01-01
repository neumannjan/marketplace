<template>
    <div v-if="shown" v-bind:class="['alert', 'alert-' + type]" role="alert">
        <slot/>
        <button type="button" class="close" v-on:click="close()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</template>
<script>
    export default {
        props: ['type'],
        data: () => ({
            shown: true
        }),
        methods: {
            close() {
                this.shown = false;
                this.$emit('close');
            }
        }
    };
</script>
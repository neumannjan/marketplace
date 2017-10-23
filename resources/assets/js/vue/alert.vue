<template>
    <div v-if="shown" v-bind:class="['alert', 'alert-' + type]" role="alert">
        <slot></slot>
        <button type="button" class="close" v-on:click="hide()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</template>
<script>
    module.exports = {
        props: ['type'],
        data: () => ({
            shown: true
        }),
        methods: {
            hide() {
                this.shown = false;
            }
        }
    };
</script>
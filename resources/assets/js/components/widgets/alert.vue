<template>
    <div v-if="shown" v-bind:class="['alert', 'alert-' + type]" role="alert"
         class="d-flex flex-row align-items-start"
         @mouseenter="hover(true)" @mouseleave="hover(false)">
        <slot/>
        <button v-if="closable" type="button" class="close ml-2" v-on:click="close()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</template>
<script>
    export default {
        name: 'alert',
        props: {
            type: {
                type: String,
                required: true
            },
            closable: {
                type: Boolean,
                default: true
            }
        },
        data: () => ({
            shown: true
        }),
        methods: {
            close() {
                this.shown = false;
                this.$emit('close');
            },
            hover(hover) {
                this.$emit('hover', hover)
            }
        }
    };
</script>
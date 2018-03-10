<template>
    <div v-bind:class="['alert', 'alert-' + type]" role="alert"
         class="d-flex flex-row align-items-start"
         @mouseenter="hover(true)" @mouseleave="hover(false)">
        <slot/>
        <button v-if="closable" type="button" class="close pl-2 ml-auto" v-on:click="close()" aria-label="Close">
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
        methods: {
            close() {
                this.$emit('close');
            },
            /**
             * @param {boolean} hover
             */
            hover(hover) {
                this.$emit('hover', hover)
            }
        }
    };
</script>
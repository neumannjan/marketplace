<template>
    <div :class="['card-footer-icons d-flex', `justify-content-${justifyContent}`]">
        <button v-for="button in buttons"
                type="button"
                @click="button.callback && !button.disabled ? button.callback() : null"
                :disabled="button.disabled ? 'true' : undefined"
                :class="['btn btn-link', buttonClass, {'btn-link-gray': gray}]">
            <icon :name="button.icon" :label="button.label"/>
        </button>
    </div>
</template>

<script>
    export default {
        name: "card-icon-footer",
        props: {
            buttonClass: String,
            gray: {
                type: Boolean,
                default: true
            },
            buttons: {
                type: Array,
                required: true,
            },
            justifyContent: {
                type: String,
                default: 'center'
            }
        }
    }
</script>

<style scoped type="text/scss" lang="scss">
    @import '~CSS/includes';

    button {
        cursor: pointer;
        line-height: 14px;
        padding: 12px;
        flex-grow: 1;
        max-width: 150px;
    }

    .card-footer-icons {
        margin: #{-$card-spacer-y} #{-$card-spacer-x};
    }
</style>
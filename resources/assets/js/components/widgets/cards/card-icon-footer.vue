<template>
    <div class="row">
        <button v-for="button in buttons" type="button" @click="button.callback ? button.callback() : null"
                :class="['col btn btn-link', buttonClass, {gray: gray}]">
            <icon :name="button.icon" :label="button.label"></icon>
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
            }
        }
    }
</script>

<style scoped type="text/scss" lang="scss">
    @import '../../../../css/includes';

    button {
        cursor: pointer;
        line-height: 14px;
        padding: 12px;

        &.gray {
            color: $gray-700;

            &:hover {
                color: $gray-900;
            }
        }
    }

    .row {
        margin: #{-$card-spacer-y} #{-$card-spacer-x};
    }
</style>
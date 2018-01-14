<template>
    <transition-group
            name="tr"
            tag="div"
            class="fixed-bottom-right d-flex flex-column-reverse">
        <button v-for="(button, index) in buttons" :key="button.id ? button.id : button.icon"
                @click="$emit('click', button)"
                :class="['btn btn-floating', `btn-floating-${index}`, button.class ? button.class : 'btn-dark']">
            <icon :name="button.icon" :label="button.label"/>
        </button>
    </transition-group>
</template>

<script>
    export default {
        name: 'floating-btns',
        props: {
            buttons: {
                type: Array,
                required: true
            },
        },
    }

</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    $height: $btn-floating-margin + $btn-floating-size;
    $duration: 300ms;
    $ease-in-quad: cubic-bezier(0.55, 0.085, 0.68, 0.53);
    $ease-out-quad: cubic-bezier(0.25, 0.46, 0.45, 0.94);

    .btn-floating {
        position: absolute;
        right: 0;
        bottom: 0;
    }

    .tr-enter, .tr-leave-to {
        position: absolute;
        transform: translateY($height) !important;
    }

    @for $i from 0 through 10 {
        .btn-floating-#{$i} {
            transform: translateY(-$height*$i);
            z-index: 1000 - $i;

            &.tr-leave-to {
                z-index: 900 - $i !important;
            }
        }
    }

    .tr-enter-active {
        position: absolute;
        transition: transform $duration $ease-out-quad, opacity $duration $ease-out-quad;
        transition-delay: $duration/2;
    }

    .tr-leave-active {
        position: absolute;
        transition: transform $duration $ease-in-quad, opacity $duration $ease-in-quad;
    }
</style>
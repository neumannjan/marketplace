<template>
    <div>
        <img v-if="displayed" v-show="ready" ref="img" :style="imgStyle" :class="imgClass">
        <slot v-if="!displayed || !ready">
            <div :class="['img-placeholder w-100 h-100', placeholderClass]">
                <icon name="image" scale="2.5"/>
            </div>
        </slot>
    </div>
</template>

<script>
    import events from 'JS/components/mixins/events';
    import 'vue-awesome/icons/image';

    export default {
        name: 'placeholder-img',
        mixins: [events],
        props: {
            src: {},
            placeholderClass: {
                type: String,
                default: ''
            },
            imgStyle: {
                default: ''
            },
            imgClass: {
                default: ''
            },
        },
        data: () => ({
            ready: false
        }),
        computed: {
            displayed() {
                return !!this.src;
            }
        },
        watch: {
            async src(src) {
                if (!src) return;

                this.ready = false;

                await this.$nextTick();

                const img = this.$refs.img;

                const enable = () => {
                    this.ready = true;
                    img.removeEventListener('load', enable);
                };

                img.addEventListener('load', enable);
                img.src = src;
            }
        }
    };
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    .img-placeholder {
        position: relative;
        background-color: $gray-300;
        color: $gray-600;

        & > * {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }
    }
</style>
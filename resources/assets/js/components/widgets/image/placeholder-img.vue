<template>
    <div :style="wrapperStyle">
        <img v-if="displayed" v-show="ready" ref="img" :alt="alt" :style="imgStyle" :class="imgClass"
             :crossorigin="crossOrigin" :width="width" :height="height">
        <template v-if="!displayed || !ready">
            <slot>
                <div :class="['img-placeholder w-100 h-100', placeholderClass]">
                    <icon name="image" scale="2.5"/>
                </div>
            </slot>
        </template>
    </div>
</template>

<script>
    import events from 'JS/components/mixins/events';
    import 'vue-awesome/icons/image';

    export default {
        name: 'placeholder-img',
        mixins: [events],
        props: {
            src: {
                type: String,
                default: ''
            },
            alt: {
                type: String
            },
            srcset: {
                type: String,
                default: ''
            },
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
            width: {},
            height: {},
        },
        data: () => ({
            ready: false
        }),
        computed: {
            displayed() {
                return !!this.src;
            },
            crossOrigin() {
                return process.env.NODE_ENV === 'development' ? 'anonymous' : undefined;
            },
            wrapperStyle() {
                let style = {};

                if (this.width) style.width = `${this.width}px`;
                if (this.height) style.height = `${this.height}px`;

                return style;
            }
        },
        watch: {
            src(val) {
                if (!val) return;

                this.load(this.src, this.srcset);
            },
            ready(val) {
                if (val)
                    this.$emit('img', this.$refs.img);
            }
        },
        methods: {
            async load(src, srcset) {
                this.ready = false;

                await this.$nextTick();

                const img = this.$refs.img;

                const enable = () => {
                    this.ready = true;
                    img.removeEventListener('load', enable);
                };

                img.addEventListener('load', enable);
                if (srcset)
                    img.srcset = srcset;

                if (src)
                    img.src = src;
            }
        },
        mounted() {
            if (this.src) {
                this.load(this.src, this.srcset);
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
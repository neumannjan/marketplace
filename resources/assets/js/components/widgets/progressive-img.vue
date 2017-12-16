<template>
    <div class="img-wrapper" :style="wrapperStyle">
        <img :class="['img-full', imgClass]" v-lazy="imgObj" :alt="alt">
        <transition name="fade">
            <div v-show="!loaded" class="img-placeholder-wrapper">
                <img :src="placeholder" :class="['img-placeholder', imgClass]" :alt="alt">
                <div class="bg-white"></div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "progressive-img",
        props: {
            src: {
                type: String,
                required: true,
            },
            placeholder: {
                type: String,
                required: true,
            },
            aspectRatio: {
                type: Number,
                required: true
            },
            imgClass: {
                type: String,
                default: ""
            },
            alt: {
                type: String,
                required: true
            }
        },
        data: () => ({
            loaded: false
        }),
        computed: {
            imgObj() {
                return {
                    src: this.src,
                    loading: this.placeholder,
                }
            },
            wrapperStyle() {
                return {
                    'padding-bottom': `${this.aspectRatio * 100}%`
                }
            }
        },
        methods: {
            onLoaded({src}) {
                if (src === this.src)
                    this.loaded = true;
            }
        },
        created() {
            this.$Lazyload.$on('loaded', this.onLoaded);
        },
        destroyed() {
            this.$Lazyload.$off('loaded', this.onLoaded);
        }
    }
</script>

<style scoped>
    .img-wrapper, .img-placeholder-wrapper {
        position: relative;
        overflow: hidden;
        z-index: 0;
    }

    .img-wrapper > *, .img-placeholder-wrapper > * {
        /*transition: 0.5s filter ease-in-out;
        will-change: filter;*/
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: auto;
    }

    .img-full[lazy=loading] {
        z-index: -2;
    }

    .img-placeholder-wrapper {
        height: 100%;
        z-index: 2;
    }

    .img-placeholder {
        transform: scale(2);
        filter: blur(30px);
    }

    .bg-white {
        background-color: white;
        z-index: 1;
    }

    .fade-leave-active {
        transition: all .5s ease;
    }

    .fade-leave-to {
        opacity: 0;
    }
</style>
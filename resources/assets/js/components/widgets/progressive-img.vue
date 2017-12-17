<template>
    <div class="img-wrapper" :style="wrapperStyle">
        <img :class="[imgClass, loadClass]" v-lazy="imgObj" :alt="alt">
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
            loaded: false,
            loadClass: 'loading'
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
                if (src !== this.src) return;

                this.loadClass = 'loaded';
            }
        },
        created() {
            this.$Lazyload.$on('loaded', this.onLoaded);
        },
        destroyed() {
            this.$Lazyload.$off('loaded', this.onLoaded);
        },
        deactivated() {
            if (this.loadClass === 'loaded') {
                this.loadClass = '';
            }
        }
    }
</script>

<style scoped>
    .img-wrapper {
        position: relative;
        overflow: hidden;
        z-index: 0;
    }

    img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: auto;
        will-change: filter, transform;
    }

    @keyframes unblur {
        from {
            filter: blur(30px);
            transform: scale(1.4);
        }

        99% {
            filter: blur(0.5px);
            transform: scale(1);
        }

        to {
            filter: none;
            transform: none;
        }
    }

    img.loading {
        filter: blur(30px);
        transform: scale(1.4);
    }

    img.loaded {
        animation: unblur .5s ease;
    }
</style>
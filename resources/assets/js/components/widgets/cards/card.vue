<template>
    <div class="card">
        <div v-if="$slots.header" class="card-header">
            <slot name="header"></slot>
        </div>
        <slot name="post-header"></slot>
        <img class="card-img-top" v-lazy="imgObj" :alt="data.title" ref="img" :height="elHeight">
        <div class="card-body">
            <h4 class="card-title">{{ data.title }}</h4>
            <p class="card-text">
                <slot></slot>
            </p>
        </div>
        <slot name="pre-footer"></slot>
        <div v-if="$slots.footer" class="card-footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "card",
        props: {
            data: {
                type: Object,
                required: true,
            }
        },
        data: () => ({
            elHeight: 0,
        }),
        computed: {
            imgObj() {
                return {
                    src: this.data.img,
                    loading: this.data.thumb,
                }
            }
        },
        mounted() {
            this.elHeight = this.data.height * this.$refs.img.offsetWidth / this.data.width;

            let handler = ({bindType, el, naturalHeight, naturalWidth, $parent, src, loading, error}, formCache) => {
                if (el === this.$refs.img) {
                    this.elHeight = 'auto';
                    this.$Lazyload.$off('loaded', handler);
                }
            };

            this.$Lazyload.$on('loaded', handler);
        }
    }
</script>

<style scoped>
    .card {
        overflow: hidden;
    }

    .card-img-top {
        transition: 0.5s filter ease-in-out;
        will-change: filter;
    }

    .card-img-top[lazy=loading] {
        filter: blur(30px);
    }
</style>
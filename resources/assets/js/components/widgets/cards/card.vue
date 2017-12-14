<template>
    <div class="card">
        <div v-if="$slots.header" class="card-header">
            <slot name="header"></slot>
        </div>
        <slot name="post-header"></slot>
        <img v-if="img" class="card-img-top" v-lazy="imgObj" :alt="title" ref="img" :height="elHeight">
        <div class="card-body">
            <h4 v-if="title" class="card-title">{{ title }}</h4>
            <h6 v-if="subtitle" class="card-subtitle mb-2 text-muted">{{ subtitle }}</h6>
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
            title: String,
            subtitle: String,
            img: String,
            thumb: String,
            width: Number,
            height: Number,
        },
        data: () => ({
            elHeight: 0,
        }),
        computed: {
            imgObj() {
                if (!this.img) return {};

                return {
                    src: this.img,
                    loading: this.thumb,
                }
            }
        },
        mounted() {
            if (this.img) {
                this.elHeight = this.height * this.$refs.img.offsetWidth / this.width;

                let handler = ({bindType, el, naturalHeight, naturalWidth, $parent, src, loading, error}, formCache) => {
                    if (el === this.$refs.img) {
                        this.elHeight = 'auto';
                        this.$Lazyload.$off('loaded', handler);
                    }
                };

                this.$Lazyload.$on('loaded', handler);
            }
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
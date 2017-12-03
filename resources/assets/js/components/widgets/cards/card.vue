<template>
    <div class="card">
        <img class="card-img-top" v-lazy="imgObj" :alt="title" ref="img" :height="elHeight">
        <div class="card-body">
            <h4 class="card-title">{{ title }}</h4>
            <p class="card-text">
                <slot></slot>
            </p>
        </div>
    </div>
</template>

<script>
    export default {
        name: "card",
        props: {
            title: {
                type: String,
                required: true
            },
            img: {
                type: String,
                required: true
            },
            thumb: String,
            width: Number,
            height: Number
        },
        data: () => ({
            elHeight: 0,
        }),
        computed: {
            imgObj() {
                return {
                    src: this.img,
                    loading: this.thumb,
                }
            }
        },
        mounted() {
            this.elHeight = this.height * this.$refs.img.offsetWidth / this.width;

            let handler = ({bindType, el, naturalHeight, naturalWidth, $parent, src, loading, error}, formCache) => {
                if (el === this.$refs.img) {
                    this.elHeight = 'auto';
                    this.$Lazyload.$off('loaded', handler);
                }
            };

            this.$Lazyload.$on('loaded', handler);
        },
        created() {

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
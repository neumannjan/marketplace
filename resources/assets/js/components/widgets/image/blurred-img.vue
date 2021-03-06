<template>
    <div class="blurred-img-wrapper">
        <canvas class="blurred-img-canvas" ref="canvas" v-show="loaded"></canvas>
        <slot v-if="!loaded"/>
    </div>
</template>

<script>
    import StackBlur from 'stackblur-canvas';
    import Vue from 'vue';

    export default Vue.extend({
        name: 'blurred-img',
        props: {
            data: {
                required: true
            },
            blur: {
                type: Number,
                default: 120
            },
            modifyCallback: {
                default: null
            }
        },
        data: () => ({
            loaded: false
        }),
        watch: {
            async data() {
                if (this.loaded) {
                    this.loaded = false;
                    await this.$nextTick();
                }

                this.load();
            }
        },
        methods: {
            load(force = false) {
                if ((!force && this.loaded) || !this.data)
                    return;

                /** @type {HTMLCanvasElement} */
                const canvas = this.$refs.canvas;
                const styles = getComputedStyle(canvas);
                canvas.width = parseFloat(styles.width ? styles.width : '0');
                canvas.height = parseFloat(styles.height ? styles.height : '0');

                const ctx = canvas.getContext('2d');

                if (ctx) {
                    ctx.drawImage(this.data, 0, 0, canvas.width, canvas.height);

                    if (this.modifyCallback)
                        ctx.putImageData(this.modifyCallback(ctx.getImageData(0, 0, canvas.width, canvas.height)), 0, 0);
                }

                StackBlur.canvasRGB(canvas, 0, 0, canvas.width, canvas.height, this.blur);

                this.$emit('blurred', canvas);

                this.loaded = true;
            }
        },
        mounted() {
            if (this.data)
                this.load();
            this.$onElResize(this.$refs.canvas, () => this.load(true));
        },
        activated() {
            this.load(true);
        }
    });
</script>

<style scoped>
    .blurred-img-wrapper {
        position: relative;
    }

    .blurred-img-canvas {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
    }
</style>
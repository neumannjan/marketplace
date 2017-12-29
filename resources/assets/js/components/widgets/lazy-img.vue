<template>
    <div class="img-wrapper" :style="wrapperStyle">
        <canvas :class="imgClass" :alt="alt" ref="canvas" :width="width" :height="height"></canvas>
    </div>
</template>

<script>
    import axios from 'axios';
    import Blur from 'stackblur-canvas';
    import Velocity from 'velocity-animate';
    import Pool from '../../tools/pool';
    import throttle from 'lodash/throttle';

    const isInViewport = (el) => {
        const rect = el.getBoundingClientRect();

        const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
        const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

        const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
        const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

        return (vertInView && horInView);
    };

    const canvasPool = new Pool(() => {
        return document.createElement('canvas');
    }, (canvas, width, height) => {
        canvas.width = width;
        canvas.height = height;
    }, 5);

    export default {
        name: "lazy-img",
        props: {
            src: {
                type: String,
                required: true,
            },
            thumb: {
                type: String,
                required: true,
            },
            width: {
                type: Number,
                required: true
            },
            height: {
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
            },
            blur: {
                type: Number,
                default: 60
            },
            duration: {
                type: Number,
                default: 600
            },
            blurIterations: {
                type: Number,
                default: 3
            }
        },
        data: () => ({
            loadingBegan: false,
            loadThrottled: null
        }),
        computed: {
            aspectRatio() {
                return this.height / this.width;
            },
            wrapperStyle() {
                return {
                    'padding-bottom': `${this.aspectRatio * 100}%`
                }
            }
        },
        methods: {
            async load() {
                if (this.loadingBegan)
                    return;

                const canvas = this.$refs.canvas;

                if (!canvas || !isInViewport(canvas))
                    return;

                this.loadingBegan = true;

                const dimensions = canvas.getBoundingClientRect();
                const width = Math.min(this.width, dimensions.width);
                const height = Math.min(this.height, dimensions.height);

                const ctx = canvas.getContext('2d');

                const response = await axios.get(this.src, {responseType: 'blob'});
                const bitmap = await createImageBitmap(response.data);

                let canvases = [];

                for (let i = 0; i < this.blurIterations; ++i) {
                    const c = canvasPool.get(width, height);
                    const cctx = c.getContext('2d');
                    canvases.push(c);

                    if (i > 0) {
                        let blur = ((this.blurIterations - i) / this.blurIterations) * this.blur;

                        cctx.drawImage(bitmap, 0, 0, this.width, this.height, 0, 0, width, height);

                        if (blur > 0) Blur.canvasRGB(c, 0, 0, c.width, c.height, blur, 2);
                    } else {
                        cctx.drawImage(canvas, 0, 0, this.width, this.height, 0, 0, width, height);
                    }
                }

                canvases.push(bitmap);

                await Velocity(canvas, {
                    tween: 1
                }, {
                    duration: this.duration,
                    easing: 'linear',
                    progress: (elements, complete, remaining, start, tweenValue) => {
                        const iteration = Math.floor(tweenValue * (this.blurIterations));

                        if (iteration < this.blurIterations) {

                            const alpha = tweenValue * this.blurIterations - iteration;

                            if (alpha !== 1) {
                                const c = canvases[iteration];
                                const c2 = canvases[iteration + 1];
                                ctx.drawImage(c, 0, 0, c.width, c.height, 0, 0, this.width, this.height);

                                ctx.globalAlpha = alpha;
                                ctx.drawImage(c2, 0, 0, c2.width, c2.height, 0, 0, this.width, this.height);
                                ctx.globalAlpha = 1;
                            } else if (iteration < this.blurIterations - 1) {
                                const c2 = canvases[iteration + 1];
                                ctx.drawImage(c2, 0, 0, c2.width, c2.height, 0, 0, this.width, this.height);
                            }
                        }
                    }
                });

                canvases.pop();
                canvasPool.release(...canvases);

                ctx.drawImage(bitmap, 0, 0);

                bitmap.close();
            }
        },
        async mounted() {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');

            const response = await axios.get(this.thumb, {responseType: 'blob'});
            const bitmap = await createImageBitmap(response.data);

            ctx.drawImage(bitmap, 0, 0, this.width, this.height);
            Blur.canvasRGB(canvas, 0, 0, this.width, this.height, this.blur, 2);
            bitmap.close();

            await this.load();

            this.loadThrottled = throttle(this.load, 200);

            if (!this.loadingBegan) {
                window.addEventListener('scroll', this.loadThrottled);
            }
        },
        destroyed() {
            window.removeEventListener('scroll', this.loadThrottled);
        },
        deactivated() {
            window.removeEventListener('scroll', this.loadThrottled);
        },
        activated() {
            if (!this.loadingBegan)
                window.addEventListener('scroll', this.loadThrottled);
        }
    }
</script>

<style scoped>
    .img-wrapper {
        position: relative;
        overflow: hidden;
        z-index: 0;
    }

    img, canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: auto;
    }
</style>
<template>
    <div class="img-wrapper" ref="wrapper" :style="wrapperStyle">
        <canvas v-if="!shown && !error" :class="imgClass" :alt="alt" ref="canvas" :width="width"
                :height="height"></canvas>
        <img v-if="inViewport && !error" v-show="shown" :src="src" :class="imgClass" :alt="alt" ref="img"
             :crossorigin="crossOrigin">
        <div class="error" v-if="error">
            <icon name="chain-broken" label="Error" :scale="3"/> <!-- TODO translate label -->
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Blur from 'stackblur-canvas';
    import Velocity from 'velocity-animate';
    import Pool from '../../tools/pool';
    import throttle from 'lodash/throttle';
    import helpers from '../../helpers';

    import 'vue-awesome/icons/chain-broken';

    const canvasPool = new Pool(() => {
        return document.createElement('canvas');
    }, (canvas, width, height) => {
        canvas.width = width;
        canvas.height = height;
    }, 5);

    const loadImagePromise = (img) => {
        if (img.complete || img.naturalWidth > 0)
            return Promise.resolve(img);

        return new Promise((resolve, reject) => {
            helpers.awaitEvent(img, 'load').then(e => resolve(img));
            helpers.awaitEvent(img, 'error').then(e => reject(e));
        });
    };

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
                default: 120
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
            inViewport: false,
            loadingBegan: false,

            shown: false,
            error: false,
        }),
        computed: {
            aspectRatio() {
                return this.height / this.width;
            },
            wrapperStyle() {
                return {
                    'padding-bottom': `${this.aspectRatio * 100}%`
                }
            },
            crossOrigin() {
                return process.env.NODE_ENV === 'development' ? 'anonymous' : 'use-credentials';
            }
        },
        methods: {
            onViewportEnter() {
                const check = () => {
                    const el = this.$refs.wrapper;

                    if (!el) {
                        this.inViewport = false;
                        return false;
                    }

                    const rect = el.getBoundingClientRect();

                    const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
                    const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

                    const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
                    const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

                    return this.inViewport = (vertInView && horInView);
                };

                const p1 = new Promise(resolve => {
                    if (check())
                        resolve();
                    else
                        this.$nextTick(() => {
                            if (check()) resolve();
                        });
                });


                const p2 = new Promise(resolve => {
                    let func;

                    const activate = () => window.addEventListener('scroll', func);
                    const deactivate = () => window.removeEventListener('scroll', func);

                    const c = () => {
                        if (check()) {
                            this.$off('activated', activate);
                            this.$off('deactivated', deactivate);
                            this.$off('destroyed', deactivate);
                            window.removeEventListener('scroll', func);
                            resolve();
                            return true;
                        }

                        return false;
                    };

                    func = throttle(c, 200);

                    if (!c()) {
                        window.addEventListener('scroll', func);
                        this.$on('activated', activate);
                        this.$on('deactivated', deactivate);
                        this.$on('destroyed', deactivate);
                    }
                });

                return Promise.race([p1, p2]);
            },
            async load(thumbRequest) {
                if (this.loadingBegan)
                    return;

                this.loadingBegan = true;

                const fullImage = this.$refs.img;

                if (this.error)
                    return;

                let thumbFailed = false;

                const loadedImage = await Promise.race([
                    loadImagePromise(fullImage).catch((e) => {
                        this.error = true;
                        console.log(e.error);
                    }),
                    thumbRequest.catch(() => {
                        thumbFailed = true;
                    }),
                ]);

                if (loadedImage === fullImage) {
                    this.shown = true;
                    return;
                }

                if (this.error)
                    return;

                const thumbImage = loadedImage;

                const canvas = this.$refs.canvas;
                const ctx = canvas.getContext('2d');

                if (!thumbFailed) {
                    ctx.drawImage(thumbImage, 0, 0, this.width, this.height);
                    Blur.canvasRGB(canvas, 0, 0, this.width, this.height, this.blur);
                }

                try {
                    await loadImagePromise(fullImage);
                }
                catch (error) {
                    this.error = true;
                    return;
                }

                const dimensions = canvas.getBoundingClientRect();
                const width = Math.min(this.width, dimensions.width);
                const height = Math.min(this.height, dimensions.height);

                let canvases = [];

                for (let i = 0; i < this.blurIterations; ++i) {
                    const c = canvasPool.get(width, height);
                    const cctx = c.getContext('2d');
                    canvases.push(c);

                    if (i > 0) {
                        let blur = ((this.blurIterations - i) / this.blurIterations) * this.blur * 0.5;

                        cctx.drawImage(fullImage, 0, 0, fullImage.width, fullImage.height, 0, 0, width, height);

                        if (blur > 0) {
                            try {
                                Blur.canvasRGB(c, 0, 0, c.width, c.height, blur);
                            }
                            catch (e) {
                            }
                        }
                    } else {
                        cctx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, width, height);
                    }
                }

                canvases.push(fullImage);

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
                            } else {
                                ctx.drawImage(fullImage, 0, 0, fullImage.width, fullImage.height, 0, 0, this.width, this.height);
                            }
                        }
                    }
                });

                canvases.pop();
                canvasPool.release(...canvases);

                this.shown = true;
            }
        },
        async mounted() {
            const thumbRequest = new Promise((resolve, reject) => {
                axios.get(this.thumb, {responseType: 'blob'})
                    .then(response => {
                        createImageBitmap(response.data)
                            .then(bitmap => resolve(bitmap))
                            .catch(reject);
                    })
                    .catch(error => {
                        if (error.response === undefined)
                            this.$store.commit('connection', false);

                        this.error = true;

                        reject();
                    });
            });

            await this.onViewportEnter();
            await new Promise(resolve => this.$nextTick(resolve));

            await this.load(thumbRequest);

            const bitmap = await thumbRequest;
            bitmap.close();
        },
        activated() {
            this.$emit('activated');
        },
        deactivated() {
            this.$emit('deactivated');
        },
        destroyed() {
            this.$emit('destroyed');
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import "../../../css/includes";

    .img-wrapper {
        position: relative;
        overflow: hidden;
        z-index: 0;
    }

    img, canvas, .error {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: auto;
    }

    .error {
        height: 100%;
        background-color: theme-color-level('danger', -5);
    }

    .error > * {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: $danger;
    }
</style>
<template>
    <div class="img-wrapper" ref="wrapper" :style="wrapperStyle">
        <canvas v-if="!shown && !error" :class="imgClass" :alt="alt" ref="canvas" :width="width"
                :height="height"></canvas>
        <img v-if="wasInViewport && !error" v-show="shown" :src="src" :class="imgClass" :alt="alt" ref="img"
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
    import throttle from 'lodash/throttle';
    import helpers from 'JS/lib/helpers';
    import appEvents,{ Events } from 'JS/events';
    import CanvasPool from 'JS/components/widgets/image/canvas-pool';
    import Vue from 'vue';

    import 'vue-awesome/icons/chain-broken';

    /**
     * @type {CanvasPool}
     */
    const canvasPool = new CanvasPool(5);

    /**
    * @param {HTMLImageElement} img
    */
    const loadImagePromise = (img) => {
        if (img.complete || img.naturalWidth > 0)
            return Promise.resolve(img);

        return new Promise((resolve, reject) => {
            helpers.awaitEvent(img, 'load').then(e => resolve(img));
            helpers.awaitEvent(img, 'error').then(e => reject(e));
        });
    };

    export default Vue.extend({
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
            wasInViewport: false,
            loadingBegan: false,
            inDOM: false,

            shown: false,
            error: false,
        }),
        computed: {
            /**
             * @returns {number}
             */
            aspectRatio() {
                return this.height / this.width;
            },
            wrapperStyle() {
                return {
                    //@ts-ignore
                    'padding-bottom': `${this.aspectRatio * 100}%`
                }
            },
            crossOrigin() {
                return process.env.NODE_ENV === 'development' ? 'anonymous' : undefined;
            }
        },
        methods: {
            inViewportCheck() {
                /** @type {HTMLElement} */
                //@ts-ignore
                const el = this.$refs.wrapper;

                if (!el || !this.inDOM) {
                    return false;
                }

                const rect = el.getBoundingClientRect();

                const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
                const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

                const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
                const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

                const result = (vertInView && horInView);

                if (result)
                    this.wasInViewport = true;

                return result;
            },
            onViewportEnter() {
                const p1 = new Promise(resolve => {
                    if (this.inViewportCheck())
                        resolve();
                    else
                        this.$nextTick(() => {
                            if (this.inViewportCheck()) resolve();
                        });
                });


                const p2 = new Promise(resolve => {
                    /** @type {() => boolean} */
                    let func;

                    const activate = () => window.addEventListener('scroll', func);
                    const deactivate = () => window.removeEventListener('scroll', func);

                    const c = () => {
                        if (this.inViewportCheck()) {
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

                const p3 = new Promise(resolve => {
                    const func = () => {
                        if (this.inViewportCheck()) {
                            appEvents.off(Events.ViewportChange, func);
                            resolve();
                        }
                    };

                    appEvents.on(Events.ViewportChange, func);
                });

                return Promise.race([p1, p2, p3]);
            },
            /**
             * @param {Promise<ImageBitmap>} thumbRequest
             */
            async load(thumbRequest) {
                if (this.loadingBegan)
                    return;

                this.loadingBegan = true;

                /**
                 * @type {HTMLImageElement}
                 */
                //@ts-ignore
                const fullImage = this.$refs.img;

                if (this.error)
                    return;

                let thumbFailed = false;

                const loadedImage = await Promise.race([
                    loadImagePromise(fullImage).catch((e) => {
                        this.error = true;
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

                /**
                 * @type {HTMLCanvasElement}
                 */
                //@ts-ignore
                let canvas = this.$refs.canvas;
                /**
                 * @type {CanvasRenderingContext2D | null}
                 */
                let ctx;

                if (canvas && !thumbFailed) {
                    ctx = canvas.getContext('2d');
                    if (ctx) {
                        ctx.drawImage(thumbImage, 0, 0, this.width, this.height);
                    }
                    Blur.canvasRGB(canvas, 0, 0, this.width, this.height, this.blur);
                }

                try {
                    await loadImagePromise(fullImage);
                }
                catch (error) {
                    this.error = true;
                    return;
                }

                if (!this.inViewportCheck()) {
                    this.shown = true;
                    return;
                }

                //@ts-ignore
                canvas = this.$refs.canvas;
                ctx = canvas.getContext('2d');

                const dimensions = canvas.getBoundingClientRect();
                const width = Math.min(this.width, dimensions.width);
                const height = Math.min(this.height, dimensions.height);

                if (width === 0 || height === 0) {
                    this.shown = true;
                    return;
                }

                /**
                 * @type {HTMLCanvasElement[]}
                 */
                let canvases = [];

                for (let i = 0; i < this.blurIterations; ++i) {
                    const c = canvasPool.get([width, height]);
                    const cctx = c.getContext('2d');
                    canvases.push(c);

                    if (i > 0) {
                        let blur = ((this.blurIterations - i) / this.blurIterations) * this.blur * 0.5;

                        if (cctx) {
                            cctx.drawImage(fullImage, 0, 0, fullImage.width, fullImage.height, 0, 0, width, height);
                        }

                        if (blur > 0) {
                            try {
                                Blur.canvasRGB(c, 0, 0, c.width, c.height, blur);
                            }
                            catch (e) {
                            }
                        }
                    } else if(cctx) {
                        cctx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, width, height);
                    }
                }

                /**
                 * @type {(HTMLCanvasElement|HTMLImageElement)[]}
                 */
                let allCanvases = canvases.slice(0);
                allCanvases.push(fullImage);

                await Velocity.animate(canvas, {
                    tween: 1
                }, {
                    duration: this.duration,
                    easing: 'linear',
                    //@ts-ignore
                    progress: (elements, complete, remaining, start, tweenValue) => {
                        const iteration = Math.floor(tweenValue * (this.blurIterations));

                        if (iteration < this.blurIterations) {

                            const alpha = tweenValue * this.blurIterations - iteration;

                            if (ctx) {
                                if (alpha !== 1) {
                                    const c = allCanvases[iteration];
                                    const c2 = allCanvases[iteration + 1];
                                    ctx.drawImage(c, 0, 0, c.width, c.height, 0, 0, this.width, this.height);

                                    ctx.globalAlpha = alpha;
                                    ctx.drawImage(c2, 0, 0, c2.width, c2.height, 0, 0, this.width, this.height);
                                    ctx.globalAlpha = 1;
                                } else if (iteration < this.blurIterations - 1) {
                                    const c2 = allCanvases[iteration + 1];
                                    ctx.drawImage(c2, 0, 0, c2.width, c2.height, 0, 0, this.width, this.height);
                                } else {
                                    ctx.drawImage(fullImage, 0, 0, fullImage.width, fullImage.height, 0, 0, this.width, this.height);
                                }
                            }
                        }
                    }
                });

                canvasPool.release(...canvases);

                this.shown = true;
            }
        },
        async mounted() {
            this.inDOM = true;

            /**
             * @type {Promise<ImageBitmap>}
             */
            const thumbRequest = new Promise((resolve, reject) => {
                axios.get(this.thumb, {responseType: 'blob'})
                    .then(response => {
                        createImageBitmap(response.data)
                            .then(bitmap => resolve(bitmap))
                            .catch(reject);
                    })
                    .catch(error => {
                        console.log(error);
                        if (error.response === undefined)
                            this.$store.commit('httpConnection', false);

                        this.error = true;

                        reject();
                    });
            });

            await this.onViewportEnter();
            await this.$nextTick();

            await this.load(thumbRequest);

            const bitmap = await thumbRequest;
            bitmap.close();
        },
        activated() {
            this.inDOM = true;
            this.$emit('activated');
        },
        deactivated() {
            this.inDOM = false;
            this.$emit('deactivated');
        },
        destroyed() {
            this.$emit('destroyed');
        }
    });
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

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
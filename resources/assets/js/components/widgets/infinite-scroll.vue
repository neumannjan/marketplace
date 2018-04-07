<template>
    <div :is="as" ref="container">
        <slot/>
    </div>
</template>

<script>
    /**
     * Portions of code taken from https://github.com/ElemeFE/vue-infinite-scroll
     */

    import throttle from 'lodash/throttle';

    const getComputedStyle = document.defaultView.getComputedStyle;

    /**
     * @param {HTMLElement} element
     */
    const getScrollEventTarget = function (element) {
        let currentNode = element;
        while (currentNode && currentNode.tagName !== 'HTML' && currentNode.tagName !== 'BODY' && currentNode.nodeType === 1) {
            const overflowY = getComputedStyle(currentNode).overflowY;
            if (overflowY === 'scroll' || overflowY === 'auto') {
                return currentNode;
            }
            //@ts-ignore
            currentNode = currentNode.parentNode;
        }
        return window;
    };

    /**
     * @param {HTMLElement | Window} element
     */
    const getScrollTop = function (element) {
        if (element === window) {
            return Math.max(window.pageYOffset || 0, document.documentElement.scrollTop);
        }

        //@ts-ignore
        return element.scrollTop;
    };

    /**
     * @param {HTMLElement | Window} element
     */
    const getVisibleHeight = function (element) {
        if (element === window) {
            return document.documentElement.clientHeight;
        }

        //@ts-ignore
        return element.clientHeight;
    };

    /**
     * @param {HTMLElement | Window} element
     */
    const getElementTop = function (element) {
        if (element === window) {
            return getScrollTop(window);
        }

        //@ts-ignore
        return element.getBoundingClientRect().top + getScrollTop(window);
    };

    export default {
        name: 'infinite-scroll',
        props: {
            distance: {
                type: Number,
                default: 200
            },
            busy: {
                type: Boolean,
                required: true
            },
            as: {
                default: 'div'
            },
            top: Boolean,
            value: {
                type: Number
            }
        },
        data: () => ({
            /** @type {(() => void) | null} */
            onScroll: null,
            scrollEventTarget: null,
            previousContainerHeight: 0,
        }),
        watch: {
            async busy(val) {
                if (this.top) {
                    const scrollEventTarget = this.getScrollEventTarget();
                    await this.$nextTick();

                    if (!val) {
                        this.setScroll(this.previousContainerHeight);
                    }

                    this.previousContainerHeight = scrollEventTarget.scrollHeight;
                }
            },
            value(val) {
                this.$nextTick(() => {
                    this.setScroll(val, false);
                });
            }
        },
        methods: {
            doRequest() {
                this.$emit('request');
            },
            /**
             * @param {number | null | false} scroll
             * @param {boolean} emit
             */
            setScroll(scroll = null, emit = true) {
                if (scroll !== undefined && scroll !== null && scroll !== false) {
                    const scrollEventTarget = this.getScrollEventTarget();
                    if (this.top) {
                        scrollEventTarget.scrollTop = scrollEventTarget.scrollHeight - scroll;
                    } else {
                        scrollEventTarget.scrollTop = scroll;
                    }

                    if (emit) {
                        this.$emit('input', scroll);
                    }
                } else if (emit) {
                    const scrollEventTarget = this.getScrollEventTarget();
                    if (this.top) {
                        this.$emit('input', scrollEventTarget.scrollHeight - scrollEventTarget.scrollTop);
                    } else {
                        this.$emit('input', scrollEventTarget.scrollTop);
                    }
                }
            },
            request() {
                if (this.busy) {
                    return;
                }

                const scrollEventTarget = this.getScrollEventTarget();

                /**
                 * @type {HTMLElement}
                 */
                    //@ts-ignore
                const element = this.$refs.container;
                const distance = this.distance;

                const viewportScrollTop = getScrollTop(scrollEventTarget);
                const viewportBottom = viewportScrollTop + getVisibleHeight(scrollEventTarget);

                let atTop = false;
                let atBottom = false;

                if (scrollEventTarget === element) {
                    atTop = viewportScrollTop <= distance;
                    atBottom = scrollEventTarget.scrollHeight - viewportBottom <= distance;
                } else {
                    const elementTop = getElementTop(element) - getElementTop(scrollEventTarget);
                    atTop = -elementTop <= distance;

                    const elementBottom = elementTop + element.offsetHeight + viewportScrollTop;
                    atBottom = viewportBottom + distance >= elementBottom;
                }

                if ((atTop && this.top) || (atBottom && !this.top)) {
                    this.doRequest();
                }

                this.$emit('top', atTop);
                this.$emit('bottom', atBottom);
                this.setScroll();
            },
            getScrollEventTarget() {
                if (this.scrollEventTarget)
                    return this.scrollEventTarget;

                //@ts-ignore
                return this.scrollEventTarget = getScrollEventTarget(this.$refs.container);
            }
        },
        mounted() {
            this.onScroll = throttle(this.request, 200);

            this.$onJS(this.getScrollEventTarget(), 'scroll', this.onScroll);
        }
    }
</script>
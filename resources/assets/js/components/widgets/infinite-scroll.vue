<template>
    <div :is="as" ref="container">
        <slot/>
    </div>
</template>

<script>
    /**
     * Portions of code taken from https://github.com/ElemeFE/vue-infinite-scroll
     */

    import events from 'JS/components/mixins/events';
    import throttle from 'lodash/throttle';

    const getComputedStyle = document.defaultView.getComputedStyle;

    const getScrollEventTarget = function (element) {
        let currentNode = element;
        while (currentNode && currentNode.tagName !== 'HTML' && currentNode.tagName !== 'BODY' && currentNode.nodeType === 1) {
            const overflowY = getComputedStyle(currentNode).overflowY;
            if (overflowY === 'scroll' || overflowY === 'auto') {
                return currentNode;
            }
            currentNode = currentNode.parentNode;
        }
        return window;
    };

    const getScrollTop = function (element) {
        if (element === window) {
            return Math.max(window.pageYOffset || 0, document.documentElement.scrollTop);
        }

        return element.scrollTop;
    };

    const getVisibleHeight = function (element) {
        if (element === window) {
            return document.documentElement.clientHeight;
        }

        return element.clientHeight;
    };

    const getElementTop = function (element) {
        if (element === window) {
            return getScrollTop(window);
        }
        return element.getBoundingClientRect().top + getScrollTop(window);
    };

    export default {
        name: 'infinite-scroll',
        mixins: [events],
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
            top: Boolean
        },
        data: () => ({
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
                        scrollEventTarget.scrollTop = scrollEventTarget.scrollHeight - this.previousContainerHeight;
                    }

                    this.previousContainerHeight = scrollEventTarget.scrollHeight;
                }
            }
        },
        methods: {
            doRequest() {
                this.$emit('request');
            },
            request() {
                if (this.busy) {
                    return;
                }

                const scrollEventTarget = this.getScrollEventTarget();
                const element = this.$refs.container;
                const distance = this.distance;

                const viewportScrollTop = getScrollTop(scrollEventTarget);
                const viewportBottom = viewportScrollTop + getVisibleHeight(scrollEventTarget);

                let shouldTrigger = false;

                if (scrollEventTarget === element) {
                    if (this.top) {
                        shouldTrigger = viewportScrollTop <= distance;
                    } else {
                        shouldTrigger = scrollEventTarget.scrollHeight - viewportBottom <= distance;
                    }
                } else {
                    if (this.top) {
                        const elementTop = getElementTop(element) - getElementTop(scrollEventTarget);
                        shouldTrigger = -elementTop <= distance;
                    } else {
                        const elementBottom = getElementTop(element) - getElementTop(scrollEventTarget)
                            + element.offsetHeight + viewportScrollTop;
                        shouldTrigger = viewportBottom + distance >= elementBottom;
                    }
                }

                if (shouldTrigger) {
                    this.doRequest();
                }
            },
            getScrollEventTarget() {
                if (this.scrollEventTarget)
                    return this.scrollEventTarget;

                return this.scrollEventTarget = getScrollEventTarget(this.$refs.container);
            }
        },
        mounted() {
            this.onScroll = throttle(this.request, 200);

            this.$onJS(this.getScrollEventTarget(), 'scroll', this.onScroll);
        }
    }
</script>
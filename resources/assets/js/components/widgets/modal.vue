<template>
    <div ref="container">
        <div class="modal-backdrop fade show" ref="backdrop"></div>
        <div class="modal fade show" role="dialog" ref="content"
             @click.self="$emit('close')"
             :style="{display: 'block', overflowX: 'hidden', overflowY: 'auto'}">
            <div :class="['modal-dialog', {[`modal-${size}`]: size}]"
                 :style="{pointerEvents: 'auto'}" role="document">
                <slot/>
            </div>
        </div>
    </div>
</template>

<script>
    import Vue from 'vue';

    let modals = 0;

    export default Vue.extend({
        name: "modal",
        props: {
            size: String,
        },
        data: () => ({
            scrollbarWidth: 0
        }),
        methods: {
            /**
             * @param {HTMLElement} to
             */
            append(to) {
                const content = this.$refs.content;
                const backdrop = this.$refs.backdrop;

                to.appendChild(backdrop);
                to.appendChild(content);
            }
        },
        mounted() {
            ++modals;

            this.$nextTick(() => {
                this.scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                document.body.style.paddingRight = `${this.scrollbarWidth}px`;
                document.body.style.overflowY = 'hidden';

                this.append(document.body);
            });
        },
        beforeDestroy() {
            --modals;

            if (modals === 0) {
                document.body.style.overflowY = '';
                document.body.style.paddingRight = '';
            }
            this.append(this.$refs.container);
        }
    });
</script>
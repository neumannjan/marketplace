<template>
    <div ref="container" v-show="false">
        <div ref="element">
            <slot/>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'dom-root',
        props: {
            id: {
                type: String,
                default: 'app'
            }
        },
        methods: {
            toRoot() {
                if (this.$refs.element) {
                    document.getElementById(this.id).appendChild(this.$refs.element);
                }
            },
            fromRoot() {
                if (this.$refs.element) {
                    if (this.$refs.container) {
                        this.$refs.container.appendChild(this.$refs.element);
                    } else {
                        this.$refs.element.parentNode.removeChild(this.$refs.element);
                    }
                }
            }
        },
        mounted() {
            this.toRoot();
        },
        activated() {
            this.toRoot();
        },
        deactivated() {
            this.toRoot();
        },
        beforeDestroy() {
            if (this.$refs.element)
                this.$refs.element.parentNode.removeChild(this.$refs.element);
        }
    };
</script>

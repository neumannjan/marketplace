<template>
    <div class="masonry" ref="masonry">
        <div class="masonry-card col-flexible" v-for="card in cards">
            <slot :data="card"/>
        </div>
        <slot name="below"/>
    </div>
</template>

<script>
    import CardComponent from './card';
    import Masonry from 'masonry-layout';
    import events from 'JS/components/mixins/events';
    import {events as appEvents} from 'JS/app';

    export default {
        name: "masonry",
        mixins: [events],
        components: {
            card: CardComponent
        },
        data: () => ({
            masonry: null,
            ready: false
        }),
        props: {
            cards: {
                type: Array,
                required: true
            },
        },
        watch: {
            cards(val, oldVal) {
                this.$nextTick(() => this.redraw(true));
            }
        },
        activated() {
            this.$nextTick(() => this.redraw());
        },
        mounted() {
            this.masonry = new Masonry(this.$refs.masonry, {
                itemSelector: '.masonry-card',
                initLayout: false,
                resize: false
            });

            this.$onElResize(this.$refs.masonry, (size) => this.redraw());

            this.masonry._positionItem = this.positionItem;

            this.masonry.on('layoutComplete', () => this.$emit('ready'));

            this.masonry.layout();
        },
        methods: {
            redraw(reloadItems = false) {
                if (this.masonry !== null) {
                    if (reloadItems)
                        this.masonry.reloadItems();
                    this.masonry.layout();

                    appEvents.$emit('viewport_change');
                }
            },
            positionItem(item, x, y, isInstant, i) {
                item.goTo(x, y);
            }
        }
    }
</script>

<style scoped>
    .masonry-card {
        margin-bottom: 30px;
    }
</style>
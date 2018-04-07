<template>
    <div class="masonry" ref="masonry">
        <div class="masonry-card col-flexible" v-for="(card, index) in cards"
             :key="card.id ? card.id : `fallback-${index}`">
            <slot :data="card"/>
        </div>
        <slot name="below"/>
    </div>
</template>

<script>
    import CardComponent from './card.vue';
    //@ts-ignore
    import Masonry from 'masonry-layout';
    import appEvents, {Events} from 'JS/events';

    export default {
        name: "masonry",
        components: {
            card: CardComponent
        },
        data: () => ({
            /** @type {object | null} */
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

            //@ts-ignore
            this.$onElResize(this.$refs.masonry, this.redraw);

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

                    appEvents.dispatch(Events.ViewportChange);
                }
            },
            //@ts-ignore
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
<template>
    <div class="masonry" ref="masonry">
        <div class="masonry-card col-md-4 col-sm-6 col-xs-12" v-for="card in cards">
            <slot :data="card"></slot>
        </div>
        <slot name="below"/>
    </div>
</template>

<script>
    import CardComponent from './card';
    import Masonry from 'masonry-layout';

    export default {
        name: "masonry",
        components: {
            card: CardComponent
        },
        data: () => ({
            masonry: null,
            ready: false,
        }),
        props: {
            cards: {
                type: Array,
                required: true
            },
        },
        watch: {
            cards(val, oldVal) {
                this.$nextTick(this.redraw);
            }
        },
        activated() {
            this.$nextTick(this.redraw);
        },
        mounted() {
            this.masonry = new Masonry(this.$refs.masonry, {
                itemSelector: '.masonry-card',
                //transitionDuration: '0.5s',
            });

            this.masonry._positionItem = this.positionItem;

            this.masonry.on('layoutComplete', () => this.$emit('ready'));
        },
        methods: {
            redraw() {
                if (this.masonry !== null) {
                    this.masonry.reloadItems();
                    this.masonry.layout();
                }
            },
            positionItem(item, x, y, isInstant, i) {
                item.goTo(x, y);

                /*// ANIMATION
                let thisNew = this.lastNew || (this.comparison.new[i] !== this.comparison.old[i]);

                    if(!thisNew) {
                        item.goTo(x, y);
                    } else {
                        let rect = this.$refs.masonry.getBoundingClientRect();
                        item.goTo(x, window.scrollY + (window.innerHeight
                            || document.documentElement.clientHeight
                            || document.body.clientHeight) + window.innerHeight);
                        item.moveTo(x, y);
                        this.lastNew = true;
                    }
                 */
            }
        }
    }
</script>

<style scoped>
    .masonry-card {
        margin-bottom: 30px;
    }

    .masonry {
        max-width: 100%;
    }
</style>
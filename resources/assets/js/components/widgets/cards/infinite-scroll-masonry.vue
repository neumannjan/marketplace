<template>
    <masonry :cards="cards" :component="component" v-infinite-scroll="request" infinite-scroll-disabled="busy"
             infinite-scroll-distance="100"></masonry>
</template>

<script>
    import api from '../../../api';
    import infiniteScroll from 'vue-infinite-scroll';
    import MasonryComponent from '../../widgets/cards/masonry';

    export default {
        props: {
            url: {
                type: String,
                required: true
            },
            component: {
                required: true
            },
            startCards: {
                type: Array,
                default: () => []
            }
        },
        components: {
            masonry: MasonryComponent
        },
        directives: {
            infiniteScroll
        },
        data: () => ({
            cards: [],
            busy: false,
            nextUrl: null
        }),
        methods: {
            request() {
                this.busy = true;

                api.URLRequest(this.nextUrl)
                    .success(result => {
                        this.addCards(result.data);
                        this.nextUrl = result['next_page_url'];
                        this.$emit('update', {
                            nextUrl: this.nextUrl,
                            cards: this.cards
                        });
                        this.busy = false;
                    })
                    .fire();
            },
            addCards(cards) {
                for (let card of cards) {
                    this.cards.push(card);
                }
            }
        },
        created() {
            this.nextUrl = this.url;
            this.cards = this.startCards;

            if (this.cards.length === 0)
                this.request();
        }
    };
</script>
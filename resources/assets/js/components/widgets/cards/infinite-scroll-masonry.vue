<template>
    <div>
        <masonry v-if="this.requestBusy || this.cards.length > 0" :cards="cards" :component="component"
                 @ready="masonryBusy = false"
                 v-infinite-scroll="request" infinite-scroll-disabled="busy"
                 infinite-scroll-distance="200">
            <template slot="below">
                <template v-if="hasMore">
                    <slot name="loading"></slot>
                </template>
            </template>
        </masonry>
        <template v-if="hasMore">
            <slot name="loading-after"></slot>
        </template>
        <template v-else="">
            <slot name="loaded"></slot>
        </template>
    </div>
</template>

<script>
    import api from '../../../api';
    import infiniteScroll from 'vue-infinite-scroll';
    import MasonryComponent from '../../widgets/cards/masonry';
    import CardComponent from '../../widgets/cards/card';

    export default {
        props: {
            url: {
                type: String,
                required: true
            },
            component: {
                required: true
            }
        },
        components: {
            masonry: MasonryComponent,
            card: CardComponent
        },
        directives: {
            infiniteScroll
        },
        computed: {
            hasMore() {
                return this.nextUrl !== undefined && this.nextUrl !== false && this.nextUrl !== null;
            },
            busy() {
                return this.requestBusy || this.masonryBusy;
            }
        },
        data: () => ({
            cards: [],
            requestBusy: false,
            masonryBusy: true,
            nextUrl: null,
            active: true
        }),
        methods: {
            request() {
                if (this.active === false || this.requestBusy === true)
                    return;

                if (!this.hasMore)
                    return false;

                this.requestBusy = true;

                api.requestByURL(this.nextUrl)
                    .then(result => {
                        this.addCards(result.data);
                        this.nextUrl = result['next_page_url'];
                        this.requestBusy = false;
                        this.masonryBusy = true;
                    });
            },
            addCards(cards) {
                this.cards = [...this.cards, ...cards];
            }
        },
        created() {
            this.nextUrl = this.url;
            this.request();
        },
        activated() {
            this.active = true;
        },
        deactivated() {
            this.active = false;
        }
    };
</script>
<template>
    <div>
        <masonry :class="masonryClass" v-if="this.requestBusy || this.cards.length > 0" :cards="cards"
                 @ready="masonryBusy = false"
                 v-infinite-scroll="request" infinite-scroll-disabled="busy"
                 infinite-scroll-distance="200">
            <template slot-scope="props">
                <slot :data="props.data"/>
            </template>
            <template slot="below">
                <template v-if="hasMore">
                    <slot name="loading"/>
                </template>
            </template>
        </masonry>
        <template v-if="hasMore">
            <slot name="loading-after"/>
        </template>
        <template v-else="">
            <slot name="loaded"/>
        </template>
    </div>
</template>

<script>
    import api from 'JS/api';
    import infiniteScroll from 'vue-infinite-scroll';
    import MasonryComponent from 'JS/components/widgets/masonry/masonry';
    import CardComponent from 'JS/components/widgets/masonry/card';

    export default {
        props: {
            url: {
                required: true
            },
            startCards: {
                type: Array,
            },
            masonryClass: {
                default: () => []
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
                        this.$emit('request', result);
                    });
            },
            addCards(cards) {
                this.cards = [...this.cards, ...cards];
            }
        },
        created() {
            this.nextUrl = this.url;
            if (!this.startCards) {
                this.request();
            } else {
                this.cards = this.startCards;
            }
        },
        activated() {
            this.active = true;
        },
        deactivated() {
            this.active = false;
        }
    };
</script>
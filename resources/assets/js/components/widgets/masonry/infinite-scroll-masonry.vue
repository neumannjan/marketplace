<template>
    <div>
        <infinite-scroll :busy="busy" @request="request" v-if="cards.length > 0">
            <masonry :class="masonryClass" :cards="cards"
                     @ready="masonryBusy = false">
                <template slot-scope="props">
                    <slot :data="props.data"/>
                </template>
                <template slot="below">
                    <template v-if="hasMore">
                        <slot name="loading"/>
                    </template>
                </template>
            </masonry>
        </infinite-scroll>
        <template v-if="hasMore">
            <slot name="loading-after"/>
        </template>
        <template v-else>
            <slot name="loaded"/>
        </template>
    </div>
</template>

<script>
    import api from 'JS/api';
    import MasonryComponent from 'JS/components/widgets/masonry/masonry';
    import CardComponent from 'JS/components/widgets/masonry/card';
    import InfiniteScroll from "JS/components/widgets/infinite-scroll";

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
            InfiniteScroll,
            masonry: MasonryComponent,
            card: CardComponent
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
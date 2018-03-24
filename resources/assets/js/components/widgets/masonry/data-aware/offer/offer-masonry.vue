<template>
    <infinite-scroll-masonry masonry-class="row" :url="url" v-model="cards">
        <offer-card slot-scope="props" :value="props.data" :show-author="showAuthor"/>
        <div slot="loading" class="masonry-card col-flexible">
            <loading-offer-card/>
        </div>
        <div slot="loaded" class="h1 text-muted text-center m-5">
            You reached the end. <!-- TODO translate -->
        </div>
    </infinite-scroll-masonry>
</template>

<script lang="ts">
    import InfiniteScrollMasonry from '../../infinite-scroll-masonry.vue';
    import OfferCard from './offer-card.vue';
    import LoadingOfferCard from './loading-offer-card.vue';
    import {Vue, Component, Prop} from 'JS/components/class-component';
    import { events, Events } from 'JS/events';
    import { Offer } from 'JS/api/types';

    @Component({
        name: 'offer-masonry',
        components: {
            InfiniteScrollMasonry,
            LoadingOfferCard,
            OfferCard
        }
    })
    export default class OfferMasonry extends Vue {
        @Prop({required: true})
        url!: string | null | false;

        @Prop({type: Array})
        startCards: any[] | undefined;

        @Prop({type: Boolean, default: true})
        showAuthor!: boolean;

        cards: any[] = [];

        created() {
            if (this.startCards) {
                this.cards = this.startCards;
            }

            this.$onEventListener(events, Events.OfferRemoved, (id: number) => {
                const index = this.cards.findIndex(card => card.id === id);

                if(index !== -1) {
                    const cards = this.cards;

                    cards.splice(index, 1);
                    
                    this.cards = cards;
                }
            });

            this.$onEventListener(events, Events.OfferModified, (offer: Offer) => {
                const index = this.cards.findIndex(card => card.id === offer.id);

                if(index !== -1) {
                    const cards = this.cards;

                    //put the card at the beginning of the masonry
                    cards.splice(index, 1)
                    cards.unshift(offer);

                    this.cards = cards;
                }
            });
        }
    }
</script>
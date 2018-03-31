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
        startCards: Offer[] | undefined;

        @Prop({type: Boolean, default: true})
        showAuthor!: boolean;

        @Prop()
        shouldShow: ((offer: Offer) => boolean) | undefined;

        cards: Offer[] = [];

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
                    let cards: Offer[] = this.cards;

                    //remove old offer
                    const [oldOffer] = cards.splice(index, 1);

                    if(!this.shouldShow || this.shouldShow(offer)) {
                        if(oldOffer.listed_at === offer.listed_at) {
                            cards = [
                                ...cards.slice(0, index),
                                offer,
                                ...cards.slice(index)
                            ];
                        } else {
                            //put the card at the beginning of the masonry
                            cards.unshift(offer);
                        }
                    }

                    this.cards = cards;
                }
            });
        }
    }
</script>
<template>
    <infinite-scroll-masonry masonry-class="row" :url="url" :start-cards="startCards">
        <offer-card slot-scope="props" :value="props.data" :show-author="showAuthor"/>
        <div slot="loading" class="masonry-card col-flexible">
            <loading-offer-card/>
        </div>
        <div slot="loaded" class="h1 text-muted text-center m-5">
            You reached the end. <!-- TODO translate -->
        </div>
    </infinite-scroll-masonry>
</template>

<script>
    import InfiniteScrollMasonry from '../../infinite-scroll-masonry.vue';
    import OfferCard from './offer-card.vue';
    import LoadingOfferCard from './loading-offer-card.vue';

    export default {
        name: 'offer-masonry',
        props: {
            url: {
                required: true
            },
            startCards: {
                type: Array,
            },
            showAuthor: {
                type: Boolean,
                default: true,
            }
        },
        components: {
            InfiniteScrollMasonry,
            LoadingOfferCard,
            OfferCard
        }
    };
</script>
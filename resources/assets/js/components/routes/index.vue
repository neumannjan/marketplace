<template>
    <div>
        <h1>{{ title }}</h1>
        <infinite-scroll-masonry url="/api/offers?status=1&author_status=1" :component="component">
            <div slot="loading" class="masonry-card col-md-4 col-sm-6 col-xs-12">
                <loading-offer-card></loading-offer-card>
            </div>
            <h1 slot="loaded" class="text-muted text-center m-5">
                You reached the end. <!-- TODO translate -->
            </h1>
        </infinite-scroll-masonry>
    </div>
</template>

<script>
    import route from '../mixins/route';
    import InfiniteScrollMasonry from '../widgets/cards/infinite-scroll-masonry';
    import OfferCard from '../widgets/cards/data-aware/offer/offer-card';
    import LoadingOfferCard from '../widgets/cards/data-aware/offer/loading-offer-card';

    export default {
        name: 'index-route',
        mixins: [route],
        components: {
            infiniteScrollMasonry: InfiniteScrollMasonry,
            loadingOfferCard: LoadingOfferCard
        },
        data: () => ({
            title: "Index page",
            component: OfferCard,
        }),
    };
</script>
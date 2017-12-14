<template>
    <div>
        <h1>Index page</h1>
        <cards url="/api/offers?status=1" :component="component"></cards>
    </div>
</template>

<script>
    import title from '../mixins/title';
    import Cards from '../widgets/cards/infinite-scroll-masonry';
    import OfferCard from '../widgets/cards/specific/offer-card';

    export default {
        mixins: [title],
        components: {
            cards: Cards,
        },
        data: () => ({
            title: "Index page",
            component: OfferCard,
        }),
    };
</script>
<template>
    <div>
        <h1>{{ title }}</h1>
        <offer-masonry url="/api/offers?status=1&author_status=1"/>
    </div>
</template>

<script>
    import route from '../mixins/route';
    import OfferMasonry from '../widgets/cards/data-aware/offer/offer-masonry';

    export default {
        name: 'index-route',
        mixins: [route],
        components: {
            OfferMasonry
        },
        data: () => ({
            title: "Index page",
        })
    };
</script>
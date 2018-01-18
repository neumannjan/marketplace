<template>
    <div>
        <h1>{{ title }}</h1>
        <offer-masonry url="/api/offers?scope=public"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import OfferMasonry from 'JS/components/widgets/cards/data-aware/offer/offer-masonry';

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
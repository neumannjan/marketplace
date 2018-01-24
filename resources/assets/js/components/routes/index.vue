<template>
    <div>
        <search class="mb-4" v-model="query" @submit="requestSearch"/>
        <offer-masonry url="/api/offers?scope=public"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import OfferMasonry from 'JS/components/widgets/cards/data-aware/offer/offer-masonry';
    import Search from "JS/components/widgets/search";

    export default {
        name: 'index-route',
        mixins: [route],
        components: {
            Search,
            OfferMasonry
        },
        data: () => ({
            title: "Index page",
            query: '',
            isTopLevelRoute: true
        }),
        methods: {
            requestSearch(query) {
                if (!query)
                    return;

                this.query = '';

                this.$router.push({
                    name: 'search',
                    params: {
                        query: query
                    }
                });
            }
        }
    };
</script>
<template>
    <div>
        <search class="mb-4" v-model="query" @submit="requestSearch"/>
        <offer-masonry url="/api/offers?scope=public"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import OfferMasonry from 'JS/components/widgets/masonry/data-aware/offer/offer-masonry.vue';
    import Search from "JS/components/widgets/search.vue";

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
            /**
             * @param {string} query
             */
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
<template>
    <div class="container">
        <search class="mb-4" @search="requestSearch"/>
        <template v-if="!loading">
            <offer-masonry v-if="results" :start-cards="results.data" :url="results.next_page_url"/>
            <div v-else class="h1 text-muted text-center">Enter to search</div>
        </template>
    </div>
</template>

<script>
    import Search from "JS/components/widgets/search";
    import route from "JS/components/mixins/route";
    import api from 'JS/api';
    import OfferMasonry from "JS/components/widgets/cards/data-aware/offer/offer-masonry";

    export default {
        name: "search-route",
        mixins: [route],
        components: {
            OfferMasonry,
            Search
        },
        data: () => ({
            query: '', //TODO validate length
            loading: false,
            results: null,
            isTopLevelRoute: true
        }),
        computed: {
            title() {
                return (this.query ? `${this.query} - ` : '') + 'Search';
            }
        },
        watch: {
            $route() {
                this.query = this.$route.query.q;
            },
            async query(newQuery, oldQuery) {
                // search performed here

                if (!newQuery) {
                    this.results = null;
                    return;
                }

                if (newQuery === oldQuery)
                    return;

                if (this.results) {
                    this.results = null;
                    await this.$nextTick();
                }

                this.loading = true;

                this.results = await api.requestSingle('search', {
                    query: newQuery
                });

                this.loading = false;
            }
        },
        methods: {
            requestSearch(query) {
                this.$router.push({query: {q: query ? query : undefined}});
            }
        },
        mounted() {
            this.query = this.$route.query.q;
        }
    }
</script>

<style scoped>

</style>
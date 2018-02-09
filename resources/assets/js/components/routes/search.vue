<template>
    <div>
        <search class="mb-4" v-model="input" @submit="requestSearch"/>
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
    import OfferMasonry from "JS/components/widgets/masonry/data-aware/offer/offer-masonry";

    export default {
        name: "search-route",
        props: {
            query: String
        },
        mixins: [route],
        components: {
            OfferMasonry,
            Search
        },
        data: () => ({
            input: '',
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
            query(newQuery, oldQuery) {
                this.performSearch(newQuery, oldQuery);
            }
        },
        methods: {
            requestSearch() {
                this.$router.push({
                    name: 'search',
                    params: {
                        query: this.input ? this.input : undefined
                    }
                });
            },
            async performSearch(query, oldQuery = null) {
                if (!query) {
                    this.results = null;
                    this.input = '';
                    return;
                }

                this.input = query;

                if (query === oldQuery)
                    return;

                if (this.results) {
                    this.results = null;
                    await this.$nextTick();
                }

                this.loading = true;

                this.results = await api.requestSingle('search', {
                    query: query
                });

                this.loading = false;
            }
        },
        mounted() {
            this.performSearch(this.query);
        }
    }
</script>

<style scoped>

</style>
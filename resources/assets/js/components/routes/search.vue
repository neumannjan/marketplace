<template>
    <div>
        <search class="mb-4" v-model="input" @submit="requestSearch"/>
        <template v-if="!loading">
            <offer-masonry v-if="results" :start-cards="results.data" :url="results.next_page_url"/>
            <div v-else class="h1 text-muted text-center">Enter to search</div>
        </template>
    </div>
</template>

<script lang="ts">
    import Search from "JS/components/widgets/search.vue";
    import route from "JS/components/mixins/route";
    import api from 'JS/api';
    import OfferMasonry from "JS/components/widgets/masonry/data-aware/offer/offer-masonry.vue";
    import Vue from "vue";
    import { PaginatedResponse, Offer } from "JS/api/types";

    export default Vue.extend({
        name: "search-route",
        props: {
            query: String
        },
        mixins: [route],
        components: {
            OfferMasonry,
            Search
        },
        data: (): {
            input: string,
            loading: boolean,
            results: PaginatedResponse<Offer> | null,
            isTopLevelRoute: boolean
        } => ({
            input: '',
            loading: false,
            results: null,
            isTopLevelRoute: true
        }),
        computed: {
            title(): string {
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
                        query: this.input ? this.input : ''
                    }
                });
            },

            async performSearch(query: string, oldQuery: string | null = null) {
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

                this.results = await api.requestSingle<PaginatedResponse<Offer>>('search', {
                    query: query
                });

                this.loading = false;
            }
        },
        mounted() {
            this.performSearch(this.query);
        }
    });
</script>

<style scoped>

</style>
<template>
    <div>
        <offer-masonry v-if="startOffers" :url="nextUrl" :start-cards="startOffers" :show-author="false"/>
    </div>
</template>

<script lang="ts">
    import api from 'JS/api';
    import router, {routeEvents, RouteEvents} from 'JS/router';
    import store,{ State } from 'JS/store';
    import {mapState} from 'vuex';
    import Vue from 'vue';
    import { User, PaginatedResponse, Offer } from 'JS/api/types';

    import route from 'JS/components/mixins/route';
    import routeFetch,{ RouteFetchMixinInterface } from 'JS/components/mixins/route-fetch';

    import OfferMasonry from 'JS/components/widgets/masonry/data-aware/offer/offer-masonry.vue';

    interface FetchResult {
        user: User | null,
        startOffers: Offer[] | null,
        nextUrl: string | null,
        shown: boolean
    }

    interface FetchParams {
        username: string
    }

    async function fetch(params: FetchParams): Promise<FetchResult | null> {
        let result = {} as FetchResult;

        const scopes = store.getters.scope;

        const response = await api.requestComposite<{
            user: User,
            offers: PaginatedResponse<Offer[]>
        }>({
            user: {
                scope: scopes.user,
                username: params.username,
            },
            offers: {
                scope: scopes.offer,
                'author/username': params.username,
            }
        });

        result.user = response.user.result;

        if (result.user === null) {
            router.replace({name: 'error'});
            return null;
        }

        result.startOffers = response.offers.result.data;
        result.nextUrl = response.offers.result.next_page_url;

        return result;
    }

    const dataDef = {
        user: null,
        startOffers: null,
        nextUrl: null,
        shown: true
    } as FetchResult;

    export default Vue.extend({
        name: 'user-route',
        mixins: [route, routeFetch(fetch, dataDef)],
        components: {
            OfferMasonry
        },
        props: {
            username: {
                type: String,
                required: true
            }
        },
        data: (): FetchResult => ({...dataDef}),
        watch: {
            user(val: User | null) {
                routeEvents.dispatch(RouteEvents.UserNavigation, val);
            },
            authenticated(auth: boolean, oldAuth: boolean) {
                if (auth !== oldAuth) {
                    const routeFetchMixin: RouteFetchMixinInterface = <any>this;
                    routeFetchMixin.doFetch();
                }
            }
        },
        computed: {
            title(): string {
                return this.user ? this.user.display_name : this.username;
            },
            isThisUser(): boolean {
                return this.$store.state.user && this.$store.state.user.username === this.username;
            },
            isTopLevelRoute() {
                return this.isThisUser;
            },
            ...mapState({
                authenticated: (state: State) => state.is_authenticated
            })
        },
    });
</script>
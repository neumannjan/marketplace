<template>
    <div>
        <offer-masonry v-if="startOffers" :url="nextUrl" :start-cards="startOffers" :show-author="false"/>
    </div>
</template>

<script lang="ts">
    import api from 'JS/api';
    import router, {routeEvents, RouteEvents} from 'JS/router';
    import store from 'JS/store';
    import {Offer, PaginatedResponse, User} from 'JS/api/types';
    import {Component, mixins, Prop, Watch} from 'JS/components/class-component';

    import route from 'JS/components/mixins/route';
    import routeFetch from 'JS/components/mixins/route-fetch';

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

    const dataDef: FetchResult = {
        user: null,
        startOffers: null,
        nextUrl: null,
        shown: true
    };


    @Component({
        name: 'user-route',
        components: {
            OfferMasonry
        },
    })
    export default class UserRoute extends mixins(route, routeFetch(fetch, dataDef)) implements FetchResult {
        @Prop({type: String, required: true})
        username!: string;

        user: User | null = null;
        startOffers: Offer[] | null = null;
        nextUrl: string | null = null;
        shown: boolean = true;

        @Watch('user')
        onUserChange(val: User | null) {
            routeEvents.dispatch(RouteEvents.UserNavigation, val);
        }

        @Watch('authenticated')
        onAuthenticatedChange(auth: boolean, oldAuth: boolean) {
            if (auth !== oldAuth) {
                this.doFetch();
            }
        }

        get title(): string {
            return this.user ? this.user.display_name : this.username;
        }

        get isThisUser(): boolean {
            return this.$store.state.user && this.$store.state.user.username === this.username;
        }

        get isTopLevelRoute(): boolean {
            return this.isThisUser;
        }

        get authenticated(): boolean {
            return !!store.state.user;
        }
    }
</script>
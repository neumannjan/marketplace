<template>
    <div>
        <offer-masonry v-if="startOffers" :url="nextUrl" :start-cards="startOffers" :show-author="false"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import routeFetch from 'JS/components/mixins/route-fetch';
    import api from 'JS/api';
    import OfferMasonry from 'JS/components/widgets/cards/data-aware/offer/offer-masonry';
    import router, {events as routeEvents} from 'JS/router';
    import store from 'JS/store';

    async function fetch(params) {
        let result = {};

        const scopes = store.getters.scope;

        const response = await api.requestMultiple({
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
            return;
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
    };

    export default {
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
        data: () => ({...dataDef}),
        watch: {
            user(val) {
                routeEvents.$emit('user-navigation', val);
            }
        },
        computed: {
            title() {
                return this.user ? this.user.display_name : this.username;
            },
            isThisUser() {
                return this.$store.state.user && this.$store.state.user.username === this.username;
            },
            isTopLevelRoute() {
                return this.isThisUser;
            }
        },
    };
</script>
<template>
    <div>
        <offer-masonry v-if="startOffers" :url="nextUrl" :start-cards="startOffers" :show-author="false"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import api from 'JS/api';
    import OfferMasonry from 'JS/components/widgets/cards/data-aware/offer/offer-masonry';
    import {events as routeEvents} from 'JS/router';

    export default {
        name: 'user-route',
        mixins: [route],
        components: {
            OfferMasonry
        },
        props: {
            username: {
                type: String,
                required: true
            }
        },
        data: () => ({
            user: null,
            startOffers: null,
            nextUrl: null
        }),
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
        methods: {
            async begin() {
                if (this.startOffers) {
                    this.startOffers = null;
                    await new Promise(resolve => this.$nextTick(resolve));
                }

                routeEvents.$emit('user-navigation', null);

                const scopes = this.$store.getters.scope;

                const result = await api.requestMultiple({
                    user: {
                        scope: scopes.user,
                        username: this.username,
                    },
                    offers: {
                        scope: scopes.offer,
                        'author/username': this.username,
                    }
                });

                this.user = result.user.result;

                if (this.user === null) {
                    this.$router.replace({name: 'error'});
                    return;
                }

                routeEvents.$emit('user-navigation', this.user);

                this.startOffers = result.offers.result.data;
                this.nextUrl = result.offers.result.next_page_url;
            }

        },
        created() {
            this.begin();
        },
        beforeRouteUpdate(to, from, next) {
            this.begin();
            next();
        },
    };
</script>
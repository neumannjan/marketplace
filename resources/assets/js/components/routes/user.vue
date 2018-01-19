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
            }
        },
        created() {
            let offersScope;

            if (this.$store.state.is_admin)
                offersScope = 'unlimited';
            else if (this.$store.state.user && this.$store.state.user.username === this.username)
                offersScope = 'owned';
            else
                offersScope = 'public';

            routeEvents.$emit('user-navigation', null);

            api.requestMultiple({
                user: {
                    scope: this.$store.state.is_admin ? 'unlimited' : 'public',
                    username: this.username,
                },
                offers: {
                    scope: offersScope,
                    'author/username': this.username,
                }
            })
                .then(result => {
                    this.user = result.user.result;

                    if (this.user === null) {
                        this.$router.replace({name: 'error'});
                        return;
                    }

                    routeEvents.$emit('user-navigation', this.user);

                    this.startOffers = result.offers.result.data;
                    this.nextUrl = result.offers.result.next_page_url;
                });
        }
    };
</script>
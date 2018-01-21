<template>
    <div class="container">
        <offer-card v-if="offer" :data="offer" :large="true"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import api from 'JS/api';
    import {events as routeEvents} from "JS/router";
    import OfferCard from 'JS/components/widgets/cards/data-aware/offer/offer-card';

    export default {
        name: "offer-route",
        mixins: [route],
        components: {
            OfferCard
        },
        props: {
            id: {
                type: Number,
                required: true,
            }
        },
        data: () => ({
            offer: null,
        }),
        computed: {
            title() {
                return this.offer ? this.offer.name : 'Offer'
            }
        },
        methods: {
            async begin() {
                if (this.offer) {
                    this.offer = null;
                    await new Promise(resolve => this.$nextTick(resolve));
                }

                routeEvents.$emit('user-navigation', null);

                const offer = await api.requestSingle('offer', {
                    scope: this.$store.getters.scope.offer,
                    id: this.id
                });

                routeEvents.$emit('user-navigation', offer.author);

                this.offer = offer;
            }

        },
        created() {
            this.begin();
        },
        beforeRouteUpdate(to, from, next) {
            this.begin();
            next();
        },
    }
</script>

<style scoped>

</style>
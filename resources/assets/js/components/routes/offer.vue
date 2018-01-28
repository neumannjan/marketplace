<template>
    <div>
        <offer-card v-if="offer" :data="offer" :large="true">
            <slot name="header-end" slot="header-end"/>
        </offer-card>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import routeGuard from 'JS/components/mixins/route-guard';
    import api from 'JS/api';
    import OfferCard from 'JS/components/widgets/cards/data-aware/offer/offer-card';

    import router from 'JS/router';

    export default {
        name: "offer-route",
        mixins: [
            route,
            routeGuard('auth', vm => (vm.$store.state.is_authenticated || !vm.offer || (vm.offer.status === 1 && !vm.offer.expired)))
        ],
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
            async fetch() {
                if (this.offer) {
                    this.offer = null;
                    await this.$nextTick();
                }

                this.offer = await api.requestSingle('offer', {
                    scope: this.$store.getters.scope.offer,
                    id: this.id
                });

                if (!this.offer) {
                    router.replace({name: 'error'});
                }
            }

        },
        created() {
            this.fetch();
        },
        beforeRouteUpdate(to, from, next) {
            this.fetch();
            next();
        },
    }
</script>
<template>
    <div>
        <offer-card v-if="offer" v-model="offer" :large="true">
            <slot name="header-end" slot="header-end"/>
        </offer-card>
    </div>
</template>

<script lang="ts">
    import route from 'JS/components/mixins/route';
    import routeGuard from 'JS/components/mixins/route-guard';
    import api from 'JS/api';
    import store from 'JS/store';
    import router from 'JS/router';
    import { Offer } from 'JS/api/types';
    import Vue from 'vue';

    import OfferCard from 'JS/components/widgets/masonry/data-aware/offer/offer-card.vue';

    interface VueData {
        offer: Offer | null
    }

    export default Vue.extend({
        name: "offer-route",
        mixins: [
            route,
            routeGuard('auth', (vm: Vue & VueData) => (store.state.is_authenticated || !vm.offer || (vm.offer.status === 1 && !vm.offer.expired)))
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
        data: (): VueData => ({
            offer: null,
        }),
        computed: {
            title(): string {
                return this.offer ? this.offer.name : 'Offer'
            }
        },
        methods: {
            async fetch() {
                if (this.offer) {
                    this.offer = null;
                    await this.$nextTick();
                }

                this.offer = await api.requestSingle<Offer>('offer', {
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
    });
</script>
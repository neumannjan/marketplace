<template>
    <div class="container">
        <offer-card v-if="offer" :data="offer" :large="true">
            <slot name="header-end" slot="header-end"/>
        </offer-card>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import api from 'JS/api';
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
            async fetch() {
                if (this.offer) {
                    this.offer = null;
                    await new Promise(resolve => this.$nextTick(resolve));
                }

                this.offer = await api.requestSingle('offer', {
                    scope: this.$store.getters.scope.offer,
                    id: this.id
                });
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

<style scoped>

</style>
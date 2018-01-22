<template>
    <offer-route :id="parseInt(offer)">
        <template slot="header-end">
            <!-- TODO translate -->
            <button type="button" aria-label="Expand" class="btn btn-link text-muted py-0 ml-auto"
                    @click="goToFull">
                <icon name="expand" label="Expand" :style="{verticalAlign: 'middle'}"/>
            </button>
            <button type="button" class="btn btn-link text-muted py-0" aria-label="Close" @click="$emit('close')">
                <icon name="times" label="Close" :style="{verticalAlign: 'middle'}"/>
            </button>
        </template>
    </offer-route>
</template>

<script>
    import OfferRoute from "JS/components/routes/offer";

    import router from 'JS/router';

    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/times';

    export default {
        components: {OfferRoute},
        name: "offer-modal",
        props: {
            offer: {}
        },
        methods: {
            goToFull() {
                router.push({name: 'offer', params: {id: this.$route.query.offer}});
            }
        }
    }
</script>

<style scoped>

</style>
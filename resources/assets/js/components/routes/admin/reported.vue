<template>
    <div>
        <offer-masonry url="/api/offers?scope=reported" :shouldShow="shouldShow" />
    </div>
</template>

<script lang="ts">
    import {Component, mixins} from "JS/components/class-component";
    import route from "JS/components/mixins/route";
    import routeGuard from "JS/components/mixins/route-guard";
    import store from "JS/store";

    import OfferMasonry from "JS/components/widgets/masonry/data-aware/offer/offer-masonry.vue";
    import {isAdminOffer, Offer} from "JS/api/types";

    @Component({
        name: 'admin-reported-route',
        components: {
            OfferMasonry
        }
    })
    export default class AdminRoute extends mixins(route, routeGuard('admin', () => store.state.is_admin)) {
        readonly isTopLevelRoute: boolean = true;
        get title(): string {
            return this.$store.getters.trans('interface.page.reported');
        }

        shouldShow = (offer: Offer) => {
            return !isAdminOffer(offer) || offer.reported_times > 0;
        }
    }
</script>
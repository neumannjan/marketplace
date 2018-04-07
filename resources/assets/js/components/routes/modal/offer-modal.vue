<template>
    <offer-route :id="parseInt(offer)">
        <template slot="header-end">
            <div class="btn-wrapper ml-auto">
                <button type="button" :title="translations.expand" class="btn btn-link text-muted py-0"
                        @click="goToFull">
                    <icon name="expand" :style="{verticalAlign: 'middle'}"/>
                </button>
                <button type="button" class="btn btn-link text-muted py-0" :title="translations.close"
                        @click="$emit('close')">
                    <icon name="times" :style="{verticalAlign: 'middle'}"/>
                </button>
            </div>
        </template>
    </offer-route>
</template>

<script>
    import OfferRoute from "JS/components/routes/offer.vue";

    import router from 'JS/router';

    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/times';

    export default {
        components: {OfferRoute},
        name: "offer-modal",
        props: {
            offer: {}
        },
        computed: {
            translations() {
                return {
                    expand: this.$store.getters.trans('interface.button.expand'),
                    close: this.$store.getters.trans('interface.button.close'),
                }
            }
        },
        methods: {
            goToFull() {
                router.push({name: 'offer', params: {id: this.$route.query.offer}});
            }
        }
    }
</script>
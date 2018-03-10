<template>
    <card class="text-muted">
        <div slot="post-header" class="img-dummy card-img-top"></div>

        <div class="placeholder-loading placeholder-loading-title"></div>
        <div class="placeholder-loading placeholder-loading-title placeholder-loading-short"></div>
        <div class="placeholder-loading"></div>
        <div class="placeholder-loading"></div>
        <div class="placeholder-loading placeholder-loading-short"></div>

        <card-icon-footer :buttons="buttons" slot="footer" class="bg-white border-light" button-class="text-muted"
                          :gray="false"/>
    </card>
</template>

<script>
    import Card from '../../card.vue';
    import CardIconFooter from '../../card-icon-footer.vue';

    import 'vue-awesome/icons/heart';
    import 'vue-awesome/icons/shopping-cart';

    export default {
        name: "loading-offer-card",
        data: () => ({
            buttons: [
                {icon: 'heart'},
                {icon: 'shopping-cart'},
                {icon: 'expand'},
            ]
        }),
        components: {
            Card,
            CardIconFooter
        }
    }
</script>

<style scoped lang="scss" type="text/scss">.img-dummy {
        height: 200px;
        position: relative;
    }
</style>
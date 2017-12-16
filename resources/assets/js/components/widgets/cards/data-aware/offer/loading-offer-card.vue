<template>
    <card class="text-muted">
        <div slot="post-header" class="img-dummy card-img-top"></div>

        <div class="loading loading-title"></div>
        <div class="loading loading-title loading-short"></div>
        <div class="loading"></div>
        <div class="loading"></div>
        <div class="loading loading-short"></div>

        <card-icon-footer :buttons="buttons" slot="footer" class="bg-white border-light" button-class="text-muted"
                          :gray="false"></card-icon-footer>
    </card>
</template>

<script>
    import Card from '../../card';
    import CardIconFooter from '../../card-icon-footer';

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

<style scoped lang="scss" type="text/scss">
    @import "../../../../../../css/includes";

    .img-dummy {
        height: 200px;
        position: relative;
    }

    @keyframes loading {
        from {
            transform: translateX(-100%)
        }
        50% {
            transform: translateX(100%)
        }
        to {
            transform: translateX(100%)
        }
    }

    .loading {
        background-color: $gray-200;
        height: 1em;
        margin-bottom: $line-height-base - 1em;
        position: relative;
        overflow: hidden;

        &:after {
            content: ' ';
            background: linear-gradient(to right, $gray-200, $gray-400, $gray-200);
            height: 100%;
            width: 300px;
            animation: loading 1s linear infinite;
            left: 0;
            top: 0;
            position: absolute;
        }
    }

    .loading-title {
        font-size: $h4-font-size;
        margin-bottom: $headings-line-height - 1em;
    }

    .loading-short {
        width: 40%;
    }

    .loading-short.loading-title {
        margin-bottom: calc(#{$headings-line-height - 1em} + #{$headings-margin-bottom});
    }
</style>
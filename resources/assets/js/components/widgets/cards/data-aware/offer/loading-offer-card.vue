<template>
    <card class="text-muted">
        <div slot="post-header" class="img-dummy card-img-top"></div>

        <!-- TODO -->
        <div class="loading loading-title"></div>
        <div class="loading loading-title loading-short"></div>
        <div class="loading"></div>
        <div class="loading"></div>
        <div class="loading loading-short"></div>

        <div slot="pre-footer" class="card-footer bg-white border-light py-1">
            <div class="row">
                <button type="button" class="col btn btn-link text-muted">
                    <div class="text-center col fa fa-heart"></div>
                </button>

                <button type="button" class="col btn btn-link text-muted">
                    <div class="text-center col fa fa-shopping-cart"></div>
                </button>
            </div>
        </div>
    </card>
</template>

<script>
    import Card from '../../card';

    export default {
        name: "loading-offer-card",
        components: {
            card: Card
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
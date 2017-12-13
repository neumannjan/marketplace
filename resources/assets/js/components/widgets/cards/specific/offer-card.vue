<template>
    <card :data="cardData">
        {{ shortDesc }}

        <div slot="pre-footer" class="card-footer py-1">
            <div class="row">
                <button type="button" class="col btn btn-link">
                    <span class="fa fa-heart"></span>
                </button>

                <button type="button" class="col btn btn-link">
                    <span class="fa fa-shopping-cart"></span>
                </button>
            </div>
        </div>
    </card>
</template>

<script>
    import CardComponent from "../card";

    export default {
        name: "offer-card",
        components: {
            card: CardComponent
        },
        props: {
            data: {
                type: Object,
                required: true
            }
        },
        computed: {
            cardData() {
                let image = this.data.images[0];

                return {
                    key: this.data.id,
                    title: this.data.name,
                    img: image['size_original'],
                    thumb: image['size_tiny'],
                    width: image['width'],
                    height: image['height'],
                };
            },
            shortDesc() {
                let desc = this.data.description.substr(0, 300);
                desc = desc.substr(0, Math.min(desc.length, desc.lastIndexOf(" ")));

                desc += '...';
                return desc;
            }
        }
    }
</script>

<style scoped type="text/scss" lang="scss">
    @import "~bootstrap/scss/functions";
    @import "~bootstrap/scss/variables";
    @import "~bootstrap/scss/mixins";

    .card-footer button {
        color: $gray-700;
        cursor: pointer;

        &:hover {
            color: $gray-900;
        }
    }
</style>
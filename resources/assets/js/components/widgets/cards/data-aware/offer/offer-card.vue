<template>
    <card v-bind="cardData">
        <a href="#" class="text-dark"><h4 class="card-title">{{ data.name }}</h4></a>
        <p class="card-text">{{ shortDesc }}</p>
        <p class="h5 card-text">{{ data.price }}</p>

        <card-icon-footer slot="footer" :buttons="buttons" :gray="true"></card-icon-footer>
    </card>
</template>

<script>
    import Card from "../../card";
    import CardIconFooter from "../../card-icon-footer";

    import 'vue-awesome/icons/heart';
    import 'vue-awesome/icons/shopping-cart';
    import 'vue-awesome/icons/expand';

    export default {
        name: "offer-card",
        components: {
            Card,
            CardIconFooter
        },
        data: () => ({
            buttons: [
                {
                    icon: 'heart',
                    label: 'Like',
                    callback: null
                },
                {
                    icon: 'shopping-cart',
                    label: 'Buy',
                    callback: null
                },
                {
                    icon: 'expand',
                    label: 'Expand',
                    callback: null
                }
            ]
        }),
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
                    alt: this.data.name,
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

<style scoped>
    a {
        text-decoration: none;
    }
</style>
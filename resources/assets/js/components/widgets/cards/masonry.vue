<template>
    <div class="masonry" v-masonry transition-duration="0.3s" item-selector=".masonry-card"
         :visible-style="{transform: 'translateY(0px)',opacity: 1}"
         :hidden-style="{transform: 'translateY(100px)',opacity: 0}">
        <div class="masonry-card col-md-4 col-sm-6 col-xs-12" v-masonry-tile v-for="card in cards">
            <component :is="component" :data="card" :key="card.key">
            </component>
        </div>
        <slot name="below"></slot>
    </div>
</template>

<script>
    import CardComponent from './card';

    export default {
        name: "masonry",
        components: {
            card: CardComponent
        },
        props: {
            cards: {
                type: Array,
                required: true
            },
            component: {
                required: true,
            }
        },
        activated() {
            this.$redrawVueMasonry();
            console.log(this.card);
        }
    }
</script>

<style scoped>
    .masonry-card {
        margin-bottom: 30px;
    }

    .masonry {
        max-width: 100%;
    }
</style>
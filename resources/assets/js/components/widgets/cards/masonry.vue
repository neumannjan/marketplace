<template>
    <div v-masonry transition-duration="0.3s" item-selector=".masonry-card">
        <div class="masonry-card col-md-4 col-sm-6 col-xs-12" v-masonry-tile v-for="card in cards">
            <card :key="card.key" :title="card.title" :img="card.img" :thumb="card.thumb" :width="card.width"
                  :height="card.height">{{ card.text }}
            </card>
        </div>
    </div>
</template>

<script>
    import CardComponent from './card.vue';

    export default {
        name: "masonry",
        components: {
            card: CardComponent
        },
        props: {
            cards: {
                type: Array,
                required: true
            }
        },
    }
</script>

<style scoped>
    .masonry-card {
        margin-bottom: 30px;
    }
</style>
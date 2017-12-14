<template>
    <div>
        <h1>Index page</h1>
        <cards :url="url" :component="component" @update="update" :startCards="startCards"></cards>
    </div>
</template>

<script>
    import title from '../mixins/title';
    import Cards from '../widgets/cards/infinite-scroll-masonry';
    import OfferCard from '../widgets/cards/specific/offer-card';
    import {mapState} from 'vuex';

    export default {
        mixins: [title],
        components: {
            cards: Cards,
        },
        data: () => ({
            title: "Index page",
            component: OfferCard,
        }),
        methods: {
            update(cardData) {
                this.$store.commit('routes/updateIndex', cardData);
            }
        },
        computed: {
            ...mapState({
                startCards: state => state.routes.index.cards,
                url: state => state.routes.index.nextUrl !== undefined ? state.routes.index.nextUrl : "/api/offers?status=1"
            }),
        }
    };
</script>
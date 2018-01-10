<template>
    <div>
        <h1>{{ title }}</h1>
        <offer-masonry v-if="startOffers" :url="nextUrl" :start-cards="startOffers" :show-author="false"/>
    </div>
</template>

<script>
    import route from 'JS/components/mixins/route';
    import api from 'JS/api';
    import OfferMasonry from 'JS/components/widgets/cards/data-aware/offer/offer-masonry';

    export default {
        name: 'user-route',
        mixins: [route],
        components: {
            OfferMasonry
        },
        props: ['username'],
        data: () => ({
            user: null,
            startOffers: null,
            nextUrl: null
        }),
        computed: {
            title() {
                return this.user ? this.user.display_name : this.username;
            }
        },
        created() {
            api.requestMultiple({
                single: {
                    type: 'user',
                    username: this.username,
                },
                offers: {
                    author_username: this.username,
                }
            })
                .then(result => {
                    this.user = result.single.result;

                    if (this.user === null) {
                        this.$router.replace({name: 'error'});
                        return;
                    }

                    this.startOffers = result.offers.result.data;
                    this.nextUrl = result.offers.result.next_page_url;
                });
        }
    };
</script>
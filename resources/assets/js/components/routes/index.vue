<template>
    <div>
        <h1>Index page</h1>
        <masonry :cards="cards" v-infinite-scroll="extend" infinite-scroll-disabled="busy"
                 infinite-scroll-distance="100"></masonry>
    </div>
</template>

<script>
    import title from './../mixins/title';
    import {mapState} from 'vuex';
    import api from '../../api';
    import infiniteScroll from 'vue-infinite-scroll';
    import MasonryComponent from '../widgets/cards/masonry';
    import axios from 'axios';

    function randomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
    }

    let list; //TODO remove

    export default {
        mixins: [title],
        components: {
            masonry: MasonryComponent
        },
        directives: {
            infiniteScroll
        },
        data: () => ({
            title: "Index page",
            cards: [],
            busy: false
        }),
        computed: {
            ...mapState({
                auth: state => state.is_authenticated
            })
        },
        methods: {
            extend() {
                if (list === undefined) return;

                this.busy = true;

                for (let i = 0; i < 9; ++i) {
                    let height = randomInt(320, 640);
                    let img = list[Math.floor(Math.random() * list.length)].id;

                    this.cards.push({
                        key: this.cards.length,
                        title: "New Item",
                        img: "https://picsum.photos/480/" + height + "/?image=" + img,
                        thumb: "https://picsum.photos/24/" + height / 20 + "/?image=" + img,
                        width: 480,
                        height: height
                    })
                }
                this.busy = false;
            }
        },
        created() {
            if (list === undefined) {
                axios.get('https://picsum.photos/list').then(response => {
                    list = response.data;
                    this.extend();
                });
            } else {
                this.extend();
            }
        }
    };
</script>
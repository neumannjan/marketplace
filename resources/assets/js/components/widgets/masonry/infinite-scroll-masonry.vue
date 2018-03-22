<template>
    <div>
        <infinite-scroll :busy="busy" @request="request" v-if="value.length > 0">
            <masonry :class="masonryClass" :cards="value"
                     @ready="masonryBusy = false">
                <template slot-scope="props">
                    <slot :data="props.data"/>
                </template>
                <template slot="below">
                    <template v-if="hasMore">
                        <slot name="loading"/>
                    </template>
                </template>
            </masonry>
        </infinite-scroll>
        <template v-if="hasMore">
            <slot name="loading-after"/>
        </template>
        <template v-else>
            <slot name="loaded"/>
        </template>
    </div>
</template>

<script lang="ts">
    import api from 'JS/api';
    import Masonry from 'JS/components/widgets/masonry/masonry.vue';
    import Card from 'JS/components/widgets/masonry/card.vue';
    import InfiniteScroll from "JS/components/widgets/infinite-scroll.vue";
    import { Vue, Component, Prop } from 'JS/components/class-component';

    @Component({
        name: 'infinite-scroll-masonry',
        components: {
            InfiniteScroll,
            Masonry,
            Card
        }
    })
    export default class InfiniteScrollMasonry extends Vue {
        @Prop({required: true})
        url!: string | null | false;

        @Prop({default: () => []})
        masonryClass!: any;

        @Prop({type: Array, required: true})
        value!: any[];

        requestBusy: boolean = false;
        masonryBusy: boolean = true;
        nextUrl: string | null | false = null;
        active: boolean = true;

        get hasMore() {
            return this.nextUrl !== undefined && this.nextUrl !== false && this.nextUrl !== null;
        }

        get busy() {
            return this.requestBusy || this.masonryBusy;
        }

        request() {
            if (this.active === false || this.requestBusy === true)
                return;

            if (!this.hasMore || !this.nextUrl)
                return;

            this.requestBusy = true;

            api.requestByURL(this.nextUrl)
                .then(result => {
                    this.addCards(result.data);
                    this.nextUrl = result['next_page_url'];
                    this.requestBusy = false;
                    this.masonryBusy = true;
                    this.$emit('request', result);
                });
        }

        addCards(value: object[]) {
            this.$emit('input', [...this.value, ...value]);
        }

        created() {
            this.nextUrl = this.url;
            if (this.value.length === 0) {
                this.request();
            }
        }

        activated() {
            this.active = true;
        }

        deactivated() {
            this.active = false;
        }
    }
</script>
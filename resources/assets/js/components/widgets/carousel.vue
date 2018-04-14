<template>
    <div class="carousel slide rounded">
        <div class="carousel-inner">
            <div v-for="(item, index) in items"
                 :key="item.key !== undefined ? item.key : index"
                 :class="['carousel-item', {'active': activeIndex === index}]">
                <slot :item="item" :index="index"/>
            </div>
        </div>
        <template v-if="items.length > 1">
            <button class="btn btn-link carousel-control-prev" @click="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">{{ translations.previous }}</span>
            </button>
            <button class="btn btn-link carousel-control-next" @click="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">{{ translations.next }}</span>
            </button>
            <ol class="carousel-indicators" aria-hidden="true">
                <li v-for="(item, index) in items"
                    :key="item.id ? item.id : `fallback-${index}`"
                    aria-hidden="true"
                    @click="activeIndex = index"
                    :class="['m-1', {'active': activeIndex === index}]">
                </li>
            </ol>
        </template>
    </div>
</template>

<script lang="ts">
    import {Component, Prop, Vue} from "JS/components/class-component";
    import {TranslationMessages} from "lang.js";

    @Component({
        name: "carousel"
    })
    export default class Carousel extends Vue {
        @Prop({type: Array, required: true})
        items!: any[]

        activeIndex: number = 0;

        get translations(): TranslationMessages {
            return {
                previous: this.$store.getters.trans('interface.button.previous'),
                next: this.$store.getters.trans('interface.button.next'),
            }
        }

        next(runByUser = true) {
            if (this.activeIndex < this.items.length - 1)
                ++this.activeIndex;
            else
                this.activeIndex = 0;
        }

        prev(runByUser = true) {
            if (this.activeIndex > 0)
                --this.activeIndex;
            else
                this.activeIndex = this.items.length - 1;
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    .carousel.rounded {
        overflow: hidden;
    }

    .carousel-indicators li {
        cursor: pointer;
    }
</style>
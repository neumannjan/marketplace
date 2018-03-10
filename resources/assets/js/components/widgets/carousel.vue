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
                <span class="sr-only">Previous</span> <!-- TODO translate -->
            </button>
            <button class="btn btn-link carousel-control-next" @click="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span> <!-- TODO translate -->
            </button>
            <ol class="carousel-indicators">
                <li v-for="(item, index) in items"
                    :key="item.id ? item.id : `fallback-${index}`"
                    @click="activeIndex = index"
                    :class="['m-1', {'active': activeIndex === index}]"></li>
            </ol>
        </template>
    </div>
</template>

<script>
    //TODO transition

    export default {
        name: "carousel",
        props: {
            items: {
                type: Array,
                required: true
            },
            timer: {
                type: Number,
                default: 5000,
            }
        },
        data: () => ({
            activeIndex: 0,
            timerRunning: true
        }),
        methods: {
            next(runByUser = true) {
                if (runByUser)
                    this.timerRunning = false;

                if (this.activeIndex < this.items.length - 1)
                    ++this.activeIndex;
                else
                    this.activeIndex = 0;
            },
            prev(runByUser = true) {
                if (runByUser)
                    this.timerRunning = false;

                if (this.activeIndex > 0)
                    --this.activeIndex;
                else
                    this.activeIndex = this.items.length - 1;
            },
            handleTimer() {
                setTimeout(() => {
                    if (!this.timerRunning || this.timer <= 0)
                        return;

                    this.next(false);
                    this.handleTimer();
                }, this.timer);
            }
        },
        mounted() {
            this.handleTimer();
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
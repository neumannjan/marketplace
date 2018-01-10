<template>
    <div class="card">
        <div v-if="$slots.header" :class="['card-header', headerClass]">
            <slot name="header"/>
        </div>
        <slot name="post-header"/>
        <a href="#">
            <lazy-img v-if="img" img-class="card-img-top" :src="img" :thumb="thumb" :alt="alt"
                      :height="height" :width="width">
            </lazy-img>
        </a>
        <div class="card-body">
            <slot/>
        </div>
        <slot name="pre-footer"/>
        <div v-if="$slots.footer" :class="['card-footer', footerClass]">
            <slot name="footer"/>
        </div>
    </div>
</template>

<script>
    import LazyImg from 'JS/components/widgets/image/lazy-img';

    export default {
        name: "card",
        components: {
            LazyImg
        },
        props: {
            img: String,
            alt: String,
            thumb: String,
            width: Number,
            height: Number,
            headerClass: String,
            footerClass: String,
        }
    }
</script>
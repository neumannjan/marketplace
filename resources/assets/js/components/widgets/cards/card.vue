<template>
    <div class="card">
        <div v-if="$slots.header" :class="['card-header', headerClass]">
            <slot name="header"></slot>
        </div>
        <slot name="post-header"></slot>
        <a href="#">
            <lazy-img v-if="img" img-class="card-img-top" :src="img" :thumb="thumb" :alt="alt"
                      :height="height" :width="width">
            </lazy-img>
        </a>
        <div class="card-body">
            <slot></slot>
        </div>
        <slot name="pre-footer"></slot>
        <div v-if="$slots.footer" :class="['card-footer', footerClass]">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
    import LazyImg from '../lazy-img';

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
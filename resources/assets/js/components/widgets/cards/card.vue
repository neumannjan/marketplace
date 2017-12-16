<template>
    <div class="card">
        <div v-if="$slots.header" class="card-header">
            <slot name="header"></slot>
        </div>
        <slot name="post-header"></slot>
        <a href="#">
            <progressive-img v-if="img" img-class="card-img-top" :src="img" :placeholder="thumb" :alt="alt"
                             :aspect-ratio="height / width">
            </progressive-img>
        </a>
        <div class="card-body">
            <slot></slot>
        </div>
        <slot name="pre-footer"></slot>
        <div v-if="$slots.footer" class="card-footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script>
    import ProgressiveImg from '../progressive-img';

    export default {
        name: "card",
        components: {
            ProgressiveImg
        },
        props: {
            img: String,
            alt: String,
            thumb: String,
            width: Number,
            height: Number,
        }
    }
</script>
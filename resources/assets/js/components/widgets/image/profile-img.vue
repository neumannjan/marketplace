<template>
    <placeholder-img img-class="rounded-circle"
                     @img="onImg"
                     :width="imgSize"
                     :height="imgSize"
                     :src="img && img.urls ? img.urls.icon : ''"
                     :srcset="img && img.urls ? `${img.urls.icon}, ${img.urls.icon_2x} 2x` : ''">
        <!-- TODO img alt --><!-- TODO translate label -->
        <icon class="profile-img-placeholder"
              name="user-circle"
              label="user"
              :scale="imgSize/16"/>
    </placeholder-img>
</template>

<script>
    import PlaceholderImg from "JS/components/widgets/image/placeholder-img.vue";
    import 'vue-awesome/icons/user-circle';

    export default {
        components: {PlaceholderImg},
        name: "profile-img",
        props: {
            imgSize: {
                type: Number,
                default: 40
            },
            img: {
                type: Object,
                required: true
            },
        },
        methods: {
            /**
             * @param {HTMLImageElement} el
             */
            onImg(el) {
                this.$emit('img', el);
            }
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    .profile-img-placeholder {
        color: $placeholder-color;
    }

    img {
        position: relative;
        flex-shrink: 0;
    }
</style>
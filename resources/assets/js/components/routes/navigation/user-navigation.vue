<template>
    <div v-if="user">
        <blurred-img v-if="userHasImg" class="user-nav-bg" :data="img" :modify-callback="modifyBG"/>
        <div v-else class="user-nav-bg"></div>
        <div class="user-nav-content px-3" :style="{
            transform: `translateY(${- (borderSize + imgSize)/2}px)`,
            marginBottom: `${- (borderSize + imgSize)/2}px`
        }">
            <div class="img-border" :style="{
                width: `${imgSize + borderSize}px`,
                height: `${imgSize + borderSize}px`}">
                <!-- TODO img alt -->
                <img ref="profileImg"
                     v-if="userHasImg"
                     :src="userImgSrc"
                     :width="imgSize"
                     :height="imgSize"
                     class="rounded-circle"
                     :crossorigin="crossOrigin"
                     :srcset="`${user.profile_image.urls.icon}, ${user.profile_image.urls.icon_2x} 2x`">
                <icon class="profile-img-placeholder"
                      v-else
                      name="user-circle"
                      label="user"
                      :scale="imgSize/16"/> <!-- TODO translate label -->
            </div>
            <h1 class="h2 text-center">{{ user.display_name }}</h1>
        </div>
    </div>
</template>

<script>
    import {events as routeEvents} from 'JS/router';
    import BlurredImg from 'JS/components/widgets/image/blurred-img';
    import events from 'JS/components/mixins/events';

    import 'vue-awesome/icons/user-circle';

    export default {
        name: 'user-route-navigation',
        mixins: [events],
        props: {
            imgSize: {
                type: Number,
                default: 40
            },
            borderSize: {
                type: Number,
                default: 10
            }
        },
        components: {
            BlurredImg
        },
        computed: {
            userHasImg() {
                return this.user && !!this.user.profile_image;
            },
            crossOrigin() {
                return process.env.NODE_ENV === 'development' ? 'anonymous' : undefined;
            }
        },
        data: () => ({
            img: null,
            user: null,
            userImgSrc: null,
        }),
        methods: {
            modifyBG(imageData) {

                function contrastImage(imgData, contrast) {  //input range [-100..100]
                    const d = imgData.data;
                    contrast = (contrast / 100) + 1;  //convert to decimal & shift range: [0..2]
                    const intercept = 128 * (1 - contrast);
                    for (let i = 0; i < d.length; i += 4) {   //r,g,b,a
                        d[i] = d[i] * contrast + intercept;
                        d[i + 1] = d[i + 1] * contrast + intercept;
                        d[i + 2] = d[i + 2] * contrast + intercept;
                    }
                    return imgData;
                }

                return contrastImage(imageData, 70);
            },
            requestImg() {
                if (!this.userHasImg) return;

                const profileImg = this.$refs.profileImg;

                const setImg = () => {
                    this.img = profileImg;
                    profileImg.removeEventListener('load', setImg);
                };

                profileImg.addEventListener('load', setImg);

                this.userImgSrc = this.user.profile_image.urls.icon;
            }
        },
        created() {
            this.$onVue(routeEvents, 'user-navigation', async user => {
                if (this.user && this.user === user) return;

                if (this.user && user) {
                    this.user = null;
                    await this.$nextTick();
                }

                this.user = user;
                this.$nextTick(this.requestImg);
            });
        }
    };
</script>

<style lang="scss" type="text/scss" scoped>
    @import "~CSS/includes";

    .user-nav-bg {
        width: 100%;
        height: 100px;
        background: $placeholder-color;

        /*&:after {
            content: ' ';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.2);
        }*/
    }

    .user-nav-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .profile-img-placeholder {
        color: $placeholder-color;
    }

    img {
        position: relative;
        flex-shrink: 0;
    }

    .img-border {
        position: relative;
        border-radius: 50%;
        background: $light;

        & > * {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    }
</style>
<template>
    <div>
        <div :style="{height: collapsibleHeight}" v-if="user"
             ref="collapsible">
            <blurred-img v-if="userHasImg" class="user-nav-bg" :data="img" :modify-callback="modifyBG"/>
            <div class="user-nav-content px-3" :style="{
                transform: `translateY(${- (borderSize + imgSize)/2}px)`,
                marginBottom: `${- (borderSize + imgSize)/2}px`
            }">
                <div v-if="userHasImg" class="img-border" :style="{
                    width: `${imgSize + borderSize}px`,
                    height: `${imgSize + borderSize}px`}">
                    <img ref="profileImg"
                         :src="userImgSrc"
                         :width="imgSize"
                         :height="imgSize"
                         class="rounded-circle"
                         :crossorigin="crossOrigin"
                         :srcset="`${user.profile_image.size_icon}, ${user.profile_image.size_icon_2x} 2x`">
                </div>
                <h1 class="h2 text-center">{{ user.display_name }}</h1>
            </div>
        </div>
    </div>
</template>

<script>
    import {events as routeEvents} from 'JS/router';
    import BlurredImg from 'JS/components/widgets/image/blurred-img';

    export default {
        name: 'user-route-navigation',
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
            collapse: false,
            collapsibleHeight: undefined,
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

                this.userImgSrc = this.user.profile_image.size_icon;
            }
        },
        created() {
            routeEvents.$once('has-user', user => {
                this.user = user;
                this.$nextTick(this.requestImg);
            });
        }
    };
</script>

<style lang="scss" type="text/scss" scoped>
    @import "~CSS/includes";

    .user-nav-bg {
        position: absolute;
        width: 100%;
        height: 100px;
        top: 0;
        left: 0;

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
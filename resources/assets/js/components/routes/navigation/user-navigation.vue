<template>
    <div v-if="user">
        <blurred-img v-if="imgEl" class="user-nav-bg" :data="imgEl" :modify-callback="modifyBG"/>
        <div v-else class="user-nav-bg"></div>
        <div :style="{
            transform: `translateY(${- (borderSize + imgSize)/2}px)`,
            marginBottom: `${- (borderSize + imgSize)/2}px`
        }">
            <div class="user-nav-content px-3">
                <div class="img-border" :style="{
                    width: `${imgSize + borderSize}px`,
                    height: `${imgSize + borderSize}px`}">
                    <profile-img :img="img ? img : {}" @img="onImg" :style="{
                        width: `${imgSize}px`,
                        height: `${imgSize}px`}"/>
                </div>
                <h1 :class="['h2 text-center', {'text-danger': isBanned}]">
                    {{ user.display_name }}
                    <span v-if="isBanned" class="badge badge-danger">Banned</span>
                </h1>
                <p :class="['text-center', isBanned ? 'text-danger' : 'text-muted']"><i>@{{ user.username }}</i></p>
                <user-menu class="mx-auto" v-model="user"/>
            </div>
            <navigation-menu-vertical class="px-4 pb-4" :buttons="verticalButtons"/>
        </div>
    </div>
</template>

<script lang="ts">
    import BlurredImg from 'JS/components/widgets/image/blurred-img.vue';
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import UserMenu from "./user-menu.vue";
    import NavigationMenuVertical, {VerticalButton} from './navigation-menu-vertical';

    import {Image, User, UserStatus} from 'JS/api/types';
    import {routeEvents, RouteEvents} from 'JS/router';
    import Vue from 'vue';

    import 'vue-awesome/icons/user';
    import 'vue-awesome/icons/cog';

    export default Vue.extend({
        name: 'user-route-navigation',
        props: {
            imgSize: {
                type: Number,
                default: 40
            },
            borderSize: {
                type: Number,
                default: 10
            },
            isThisUser: {
                type: Boolean,
                default: false
            }
        },
        components: {
            ProfileImg,
            BlurredImg,
            UserMenu,
            NavigationMenuVertical
        },
        watch: {
            thisUser(user: User | null) {
                if (this.thisUser) {
                    this.user = this.thisUser;
                }
            }
        },
        computed: {
            img(): Image | null {
                if (this.user && this.user.profile_image) {
                    return this.user.profile_image;
                } else {
                    return null;
                }
            },
            crossOrigin() {
                return process.env.NODE_ENV === 'development' ? 'anonymous' : undefined;
            },
            isBanned(): boolean {
                return !!this.user && this.user.status === UserStatus.Banned;
            },
            thisUser(): User | null {
                const thisUser: User | null = this.$store.state.user;
                return this.isThisUser || (this.user && thisUser && this.user.username == thisUser.username) ? thisUser : null;
            },
            verticalButtons(): VerticalButton[] {
                if (!this.user || !this.$store.state.user || this.user.username !== this.$store.state.user.username)
                    return [];

                return [
                    {
                        icon: 'user',
                        label: this.$store.getters.trans('interface.page.me'),
                        location: {
                            name: 'user',
                            params: {
                                username: this.user.username
                            }
                        },
                    },
                    {
                        icon: 'cog',
                        label: this.$store.getters.trans('interface.page.user-settings'),
                        location: {
                            name: 'user-settings'
                        }
                    }
                ];
            }
        },
        data: (): {
            user: User | null,
            imgEl: HTMLElement | null
        } => ({
            user: null,
            imgEl: null
        }),
        methods: {
            modifyBG(imageData: ImageData) {

                function contrastImage(imgData: ImageData, contrast: number) {  //input range [-100..100]
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
            onImg(el: HTMLElement) {
                this.imgEl = el;
            }
        },
        created() {
            if (this.thisUser)
                this.user = this.thisUser;

            this.$onEventListener(routeEvents, RouteEvents.UserNavigation, async user => {
                if (this.user && this.user === user) return;

                if (this.user && user) {
                    this.user = null;
                    this.imgEl = null;
                    await this.$nextTick();
                }

                this.user = user;
            });
        }
    });
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
<template>
    <card :class="[color('border-')]">
        <div v-if="showAuthor" slot="header" class="offer-card-header">
            <router-link :to="toAuthor" class="offer-card-header text-dark">
                <profile-img :img="profileImage ? profileImage : {}" :img-size="32"/>
                <span class="ml-2 author-info">
                        {{ data.author.display_name }} <small
                        class="text-muted">{{ `@${data.author.username}` }}</small>
                    </span>
            </router-link>
            <slot name="header-end"/>
        </div>

        <template v-if="!large">

            <router-link v-if="imgData" :to="toOffer" slot="post-header">
                <lazy-img img-class="card-img-top" v-bind="imgData"/>
            </router-link>

            <router-link :to="toOffer" :class="[color('text-', 'dark')]">
                <h4 class="card-title">
                    <span>{{ data.name }} </span>
                    <badge class="ml-1 badge" v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
                </h4>
            </router-link>

            <p class="card-text">{{ shortDesc }}</p>
            <p class="h5 card-text">{{ data.price }}</p>

        </template>
        <div v-else class="row">

            <div class="col-md-5 mb-3">
                <alert v-if="isAwaitingImages" type="warning">
                    Not all images have appeared yet.
                    <a href="#" @click.prevent="refreshImages" class="alert-link">Check again if they are ready?</a>
                </alert>
                <carousel v-if="this.data.images" :items="this.data.images">
                    <template slot-scope="props">
                        <lazy-img :width="props.item.width" :height="props.item.height"
                                  v-if="props.item.ready"
                                  :src="props.item.urls.original"
                                  :thumb="props.item.urls.tiny"
                                  alt="Image"/> <!-- TODO alt -->
                    </template>
                </carousel>
            </div>

            <div class="col-md-7 d-flex flex-column">
                <h1 :class="['card-title heading-resp', color('text-', 'dark')]">
                    <span>{{ data.name }} </span>
                    <badge class="ml-1 badge" v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
                </h1>

                <p v-if="data.description" class="card-text">{{ data.description }}</p>

                <p class="price-resp card-text mt-auto">{{ data.price }}</p>
                <div class="btn-group btn-group-lg" role="group" aria-label="Basic example">
                    <button type="button"
                            v-for="button in buttons"
                            :key="button.icon"
                            @click="button.callback ? button.callback() : null"
                            :disabled="button.disabled"
                            :class="['btn', color('btn-', 'primary', true)]">
                        <icon :name="button.icon" :label="button.label"/>
                    </button>
                </div>
            </div>

        </div>

        <card-icon-footer v-if="!large" slot="footer" :buttons="buttons" :gray="true"/>
    </card>
</template>

<script>
    import Card from "../../card";
    import CardIconFooter from "../../card-icon-footer";
    import Badge from 'JS/components/widgets/badge'
    import Carousel from 'JS/components/widgets/carousel';
    import {events as appEvents} from 'JS/app';

    import router from 'JS/router';
    import api from 'JS/api';

    import 'vue-awesome/icons/shopping-cart';
    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/user-circle';
    import Alert from "JS/components/widgets/alert";
    import ProfileImg from "JS/components/widgets/image/profile-img";

    export default {
        name: "offer-card",
        components: {
            ProfileImg,
            Alert,
            Card,
            CardIconFooter,
            Badge,
            Carousel
        },
        props: {
            data: {
                type: Object,
                required: true
            },
            showAuthor: {
                type: Boolean,
                default: true,
            },
            large: {
                type: Boolean,
                default: false,
            }
        },
        methods: {
            color(prefix = '', defaultColor = null, important = false) {
                const doReturn = (value = defaultColor) => {
                    if (!value)
                        return undefined;
                    return prefix + value;
                };

                if (!this.data)
                    return doReturn();

                let badges = [];

                switch (this.data.status) {
                    case 0: // draft
                        return doReturn('warning');
                    case 2: // sold
                        return doReturn(important ? 'info' : defaultColor)
                }

                if (this.data.expired)
                    return doReturn('danger');

                return doReturn();
            },
            refreshImages() {
                if (!this.data) return;

                api.requestSingle('offer', {
                    id: this.data.id,
                    scope: this.$store.getters.scope.offer
                }).then(result => {
                    this.data = result;
                });
            }
        },
        computed: {
            isThisUser() {
                return this.$store.state.user && this.$store.state.user.username === this.data.author.username;
            },
            buttons() {
                const ubiquitous = [
                    {
                        icon: 'shopping-cart',
                        label: 'Buy',
                        disabled: this.isThisUser || !this.$store.state.is_authenticated,
                        callback: () => {
                            //TODO translate
                            if (!confirm(`Are you sure you want to send user ${this.data.author.display_name} a message?`)) {
                                return;
                            }

                            appEvents.$emit('request-popup', {
                                type: 'chat',
                                then: () => {
                                    appEvents.$emit('request-buy', this.data);
                                }
                            });
                        }
                    },
                ];

                const nonLarge = [
                    {
                        icon: 'expand',
                        label: 'Expand',
                        callback: () => router.push(this.toOffer)
                    },
                ];

                if (this.large)
                    return ubiquitous;
                else
                    return [...ubiquitous, ...nonLarge];
            },
            imgData() {
                if (!this.data || this.data.images.length === 0)
                    return null;

                let image = this.data.images[0];

                return {
                    alt: this.data.name,
                    src: image.urls.original,
                    thumb: image.urls.tiny,
                    width: image['width'],
                    height: image['height'],
                };
            },
            isAwaitingImages() {
                if (!this.data || this.data.images.length === 0)
                    return false;

                for (let img of this.data.images) {
                    if (img.ready === false)
                        return true;
                }

                return false;
            },
            shortDesc() {
                if (!this.data.description)
                    return '';

                let desc = this.data.description;

                if (desc.length < 300)
                    return desc;

                desc = desc.substr(0, 300).substr(0, Math.min(desc.length, desc.lastIndexOf(" ")));

                if (desc)
                    desc += '...';
                return desc;
            },
            profileImage() {
                if (this.data.author.profile_image) {
                    return this.data.author.profile_image;
                }

                return null;
            },
            toAuthor() {
                return {
                    name: 'user',
                    params: {
                        username: this.data.author.username
                    }
                }
            },
            toOffer() {
                return {
                    query: {
                        ...this.$route.query,
                        offer: this.data.id
                    }
                }
            },
            badges() {
                // TODO translate

                if (!this.data)
                    return null;

                let badges = [];

                switch (this.data.status) {
                    case 0:
                        badges.push({message: 'Draft', type: 'warning'});
                        break;
                    case 2:
                        badges.push({message: 'Sold', type: 'info'});
                        break;
                }

                if (this.data.expired)
                    badges.push({message: 'Expired', type: 'danger'});

                return badges;
            }
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import '~CSS/includes';

    a {
        text-decoration: none;
    }

    .badge {
        vertical-align: bottom;
    }

    .offer-card-header {
        line-height: 1em;
        display: flex;
        align-items: center;
    }

    .profile-img {
        display: block;
        float: left;
        min-width: 32px;
        height: 32px;
    }

    .author-info {
        word-wrap: normal;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .heading-resp {
        font-size: $h3-font-size;
        @include media-breakpoint-up('sm') {
            font-size: $h1-font-size;
        }
    }

    .price-resp {
        font-size: $h4-font-size;
        @include media-breakpoint-up('sm') {
            font-size: $h2-font-size;
        }
    }
</style>
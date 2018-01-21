<template>
    <card :class="[color('border-')]">
        <div v-if="showAuthor" slot="header" class="offer-card-header">
            <router-link :to="toAuthor" class="offer-card-header text-dark">
                <img v-if="profileImage" class="profile-img rounded-circle"
                     :src="profileImage['1x']"
                     :srcset="`${profileImage['1x']}, ${profileImage['2x']} 2x`"/>
                <div v-else="" class="profile-img profile-img-placeholder">
                    <icon name="user-circle" scale="2"/>
                </div>
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
                <carousel :items="this.data.images">
                    <template slot-scope="props">
                        <lazy-img :width="props.item.width" :height="props.item.height"
                                  :src="props.item.size_original"
                                  :thumb="props.item.size_tiny"
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
                            :disabled="isThisUser ? true : undefined"
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

    import router from 'JS/router';

    import 'vue-awesome/icons/heart';
    import 'vue-awesome/icons/shopping-cart';
    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/user-circle';

    export default {
        name: "offer-card",
        components: {
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
            }
        },
        computed: {
            isThisUser() {
                return this.$store.state.user && this.$store.state.user.username === this.data.author.username;
            },
            buttons() {
                const ubiquitous = [
                    {
                        icon: 'heart',
                        label: 'Like',
                        disabled: this.isThisUser,
                        callback: null
                    },
                    {
                        icon: 'shopping-cart',
                        label: 'Buy',
                        disabled: this.isThisUser,
                        callback: null
                    },
                ];

                const nonLarge = [
                    {
                        icon: 'expand',
                        label: 'Expand',
                        disabled: this.isThisUser,
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
                    src: image['size_original'],
                    thumb: image['size_tiny'],
                    width: image['width'],
                    height: image['height'],
                };
            },
            shortDesc() {
                let desc = this.data.description.substr(0, 300);
                desc = desc.substr(0, Math.min(desc.length, desc.lastIndexOf(" ")));

                if (desc)
                    desc += '...';
                return desc;
            },
            profileImage() {
                if (this.data.author.profile_image) {
                    return {
                        '1x': this.data.author.profile_image.size_icon,
                        '2x': this.data.author.profile_image.size_icon_2x,
                    }
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

    .profile-img-placeholder {
        color: $placeholder-color;
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
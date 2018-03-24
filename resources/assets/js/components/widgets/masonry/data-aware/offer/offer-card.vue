<template>
    <card :class="[color('border-')]">
        <div v-if="showAuthor" slot="header" class="offer-card-header">
            <router-link :to="toAuthor" class="offer-card-header text-dark">
                <profile-img :img="profileImage ? profileImage : {}" :img-size="32"/>
                <span class="ml-2 author-info">
                        {{ value.author.display_name }} <small
                        class="text-muted">{{ `@${value.author.username}` }}</small>
                    </span>
            </router-link>
            <slot name="header-end"/>
        </div>

        <template v-if="!large">

            <router-link v-if="imgData" :to="toOffer" slot="post-header">
                <lazy-img img-class="card-img-top" v-bind="imgData"/>
            </router-link>

            <h4 class="card-title d-flex align-items-baseline">
                <router-link :to="toOffer" :class="[color('text-', 'dark')]" style="flex-grow: 1">
                    <span>{{ value.name }} </span>
                    <badge class="ml-1 badge" v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
                </router-link>
                <b-dropdown class="ml-3" toggle-class="btn-link-gray" right variant="link" no-caret boundary="window">
                    <offer-dropdown-contents :offer="value" />

                    <icon slot="button-content" name="ellipsis-v" label="" /> <!-- TODO label -->
                </b-dropdown>
            </h4>

            <p class="card-text">{{ shortDesc }}</p>
            <p class="h5 card-text">{{ value.price }}</p>

        </template>
        <div v-else class="row">

            <div class="col-md-5 mb-3">
                <alert v-if="isAwaitingImages" type="warning">
                    Not all images have appeared yet.
                    <a href="#" @click.prevent="refreshImages" class="alert-link">Check again if they are ready?</a>
                </alert>
                <carousel v-if="this.value.images" :items="this.value.images">
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
                    <span>{{ value.name }} </span>
                    <badge class="ml-1 badge" v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
                </h1>

                <div class="card-text">
                    <p v-if="value.description">{{ value.description }}</p>
                    <p class="price-resp card-text mt-auto">{{ value.price }}</p>
                </div>

                <div class="btn-group btn-group-lg mt-1" role="group" aria-label="Basic example">
                    <button type="button"
                            v-for="button in buttons"
                            :key="button.icon"
                            @click="button.callback ? button.callback() : null"
                            :disabled="button.disabled"
                            :class="['btn', color('btn-', 'primary', true)]">
                        <icon :name="button.icon" :label="button.label"/>
                    </button>
                    <b-dropdown boundary="window" :variant="color('', 'primary', true)">
                        <offer-dropdown-contents :offer="value" />
                    </b-dropdown>
                </div>
            </div>

        </div>

        <card-icon-footer v-if="!large" slot="footer" :buttons="buttons" :gray="true"/>
    </card>
</template>

<script>
    import Card from "../../card.vue";
    import CardIconFooter from "../../card-icon-footer.vue";
    import Badge from 'JS/components/widgets/badge.vue';
    import Carousel from 'JS/components/widgets/carousel.vue';
    import Alert from "JS/components/widgets/alert.vue";
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import Popper from "JS/components/widgets/popper.vue";
    import BDropdown from "bootstrap-vue/src/components/dropdown/dropdown";
    import BDropdownItem from "bootstrap-vue/src/components/dropdown/dropdown-item";
    import BDropdownItemButton from "bootstrap-vue/src/components/dropdown/dropdown-item-button";
    import BDropdownHeader from "bootstrap-vue/src/components/dropdown/dropdown-header";
    import OfferDropdownContents from 'JS/components/widgets/masonry/data-aware/offer/offer-dropdown-contents.vue';

    import appEvents,{ Events } from 'JS/events';
    import router from 'JS/router';
    import api from 'JS/api';

    import 'vue-awesome/icons/shopping-cart';
    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/user-circle';
    import 'vue-awesome/icons/ellipsis-v';

    export default {
        name: "offer-card",
        components: {
            ProfileImg,
            Alert,
            Card,
            CardIconFooter,
            Badge,
            Carousel,
            Popper,
            BDropdown,
            OfferDropdownContents
        },
        props: {
            value: {
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
        data: () => ({
            /** @type {HTMLElement | null} */
            dropdownButtonEl: null
        }),
        methods: {
            /**
             * @param {string} prefix
             * @param {string | null} defautColor
             * @param {boolean} important
             */
            color(prefix = '', defaultColor = null, important = false) {
                /**
                 * @param {string | null} value
                 */
                const doReturn = (value = defaultColor) => {
                    if (!value)
                        return undefined;
                    return prefix + value;
                };

                if (!this.value)
                    return doReturn();

                let badges = [];

                switch (this.value.status) {
                    case 0: // draft
                        return doReturn('warning');
                    case 2: // sold
                        return doReturn(important ? 'info' : defaultColor)
                }

                if (this.value.expired)
                    return doReturn('danger');

                return doReturn();
            },
            refreshImages() {
                if (!this.value) return;

                api.requestSingle('offer', {
                    id: this.value.id,
                    scope: this.$store.getters.scope.offer
                }).then(result => {
                    this.$emit('input', result);
                });
            }
        },
        computed: {
            /**
             * @returns {boolean}
             */
            isThisUser() {
                return this.$store.state.user && this.$store.state.user.username === this.value.author.username;
            },

            /**
             * @returns {object}
             */
            buttons() {
                const ubiquitous = [
                    {
                        icon: 'shopping-cart',
                        label: 'Buy',
                        disabled: this.isThisUser || !this.$store.state.user,
                        callback: () => {
                            //TODO translate
                            if (!confirm(`Are you sure you want to send user ${this.value.author.display_name} a message?`)) {
                                return;
                            }

                            appEvents.dispatch(Events.RequestPopup, {
                                type: 'chat',
                                then: () => {
                                    appEvents.dispatch(Events.RequestBuy, this.value);
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
                if (!this.value || this.value.images.length === 0)
                    return null;

                let image = this.value.images[0];

                return {
                    alt: this.value.name,
                    src: image.urls.original,
                    thumb: image.urls.tiny,
                    width: image['width'],
                    height: image['height'],
                };
            },
            isAwaitingImages() {
                if (!this.value || this.value.images.length === 0)
                    return false;

                for (let img of this.value.images) {
                    if (img.ready === false)
                        return true;
                }

                return false;
            },
            shortDesc() {
                if (!this.value.description)
                    return '';

                let desc = this.value.description;

                if (desc.length < 300)
                    return desc;

                desc = desc.substr(0, 300).substr(0, Math.min(desc.length, desc.lastIndexOf(" ")));

                if (desc)
                    desc += '...';
                return desc;
            },
            profileImage() {
                if (this.value.author.profile_image) {
                    return this.value.author.profile_image;
                }

                return null;
            },
            toAuthor() {
                return {
                    name: 'user',
                    params: {
                        username: this.value.author.username
                    }
                }
            },
            toOffer() {
                return {
                    query: {
                        ...this.$route.query,
                        offer: this.value.id
                    }
                }
            },
            badges() {
                // TODO translate

                if (!this.value)
                    return null;

                let badges = [];

                switch (this.value.status) {
                    case 0:
                        badges.push({message: 'Draft', type: 'warning'});
                        break;
                    case 2:
                        badges.push({message: 'Sold', type: 'info'});
                        break;
                }

                if (this.value.expired)
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
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
                <lazy-img img-class="card-img-top" v-bind="imgData" :alt="translations.image"/>
            </router-link>

            <h4 class="card-title d-flex align-items-baseline">
                <router-link :to="toOffer" :class="[color('text-', 'dark'), 'ellipsis']" style="flex-grow: 1">
                    <span>{{ value.name }} </span>
                    <badge class="ml-1 badge" v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
                    <badge class="ml-1 badge"
                           v-if="reportedTimes > 0"
                           type="danger"
                           aria-hidden="true"
                           :aria-label="translations.reported">
                        <icon class="mr-1" name="flag" :scale="0.8"/>
                        {{ reportedTimes }}
                    </badge>
                </router-link>
                <b-dropdown v-if="loggedIn" :title="translations.dropdown" class="ml-3" toggle-class="btn-link-gray"
                            right variant="link" no-caret boundary="window">
                    <offer-dropdown-contents :offer="value"/>
                    <icon slot="button-content" name="ellipsis-v"/>
                </b-dropdown>
            </h4>

            <pre class="card-text offer-text-content">{{ shortDesc }}</pre>
            <p class="h5 card-text">{{ price }}</p>

        </template>
        <div v-else class="row">
            <div class="col-md-5 mb-3">
                <alert v-if="isAwaitingImages" type="warning">
                    {{ translations.loading.notice }}
                    <a href="#" @click.prevent="refreshImages" class="alert-link">{{ translations.loading.button }}</a>
                </alert>
                <carousel v-if="this.value.images" :items="this.value.images">
                    <template slot-scope="props">
                        <lazy-img :width="props.item.width" :height="props.item.height"
                                  v-if="props.item.ready"
                                  :src="props.item.urls.original"
                                  :thumb="props.item.urls.tiny"
                                  :alt="translations.image"/>
                    </template>
                </carousel>
            </div>

            <div class="col-md-7 d-flex flex-column">
                <h1 :class="['card-title heading-resp', color('text-', 'dark')]">
                    <span>{{ value.name }} </span>
                    <badge class="ml-1 badge" v-for="(badge, index) in badges" :key="index" v-bind="badge"/>
                    <badge class="ml-1 badge"
                           v-if="reportedTimes > 0"
                           type="danger"
                           aria-hidden="true"
                           :aria-label="translations.reported">
                        <icon class="mr-1" name="flag" :scale="1.2"/>
                        {{ reportedTimes }}
                    </badge>
                </h1>

                <div class="card-text">
                    <pre v-if="value.description" class="offer-text-content">{{ value.description }}</pre>
                    <p class="price-resp card-text mt-auto">{{ price }}</p>
                </div>

                <div class="btn-group btn-group-lg mt-1" role="group" aria-label="Basic example">
                    <button type="button"
                            v-for="button in buttons"
                            :title="button.label"
                            :key="button.icon"
                            @click="button.callback ? button.callback() : null"
                            :disabled="button.disabled"
                            :class="['btn', color('btn-', 'primary', true)]">
                        <icon :name="button.icon"/>
                    </button>
                    <b-dropdown v-if="loggedIn" boundary="window" :variant="color('', 'primary', true)"
                                :title="translations.dropdown">
                        <offer-dropdown-contents :offer="value"/>
                    </b-dropdown>
                </div>
            </div>

        </div>

        <card-icon-footer v-if="!large" slot="footer" :buttons="buttons" :gray="true"/>
    </card>
</template>

<script lang="ts">
    import Card from "../../card.vue";
    import CardIconFooter from "../../card-icon-footer.vue";
    import BadgeComponent from 'JS/components/widgets/badge.vue';
    import Carousel from 'JS/components/widgets/carousel.vue';
    import Alert from "JS/components/widgets/alert.vue";
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import Popper from "JS/components/widgets/popper.vue";
    import BDropdown from "bootstrap-vue/src/components/dropdown/dropdown";
    import OfferDropdownContents from 'JS/components/widgets/masonry/data-aware/offer/offer-dropdown-contents.vue';

    import appEvents, {Events, events} from 'JS/events';
    import router from 'JS/router';
    import api from 'JS/api';
    import store from "JS/store";
    import Vue from 'vue';
    import {Image, isAdminOffer, Offer} from "JS/api/types";
    import {Location} from "vue-router";

    import 'vue-awesome/icons/shopping-cart';
    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/user-circle';
    import 'vue-awesome/icons/ellipsis-v';
    import 'vue-awesome/icons/flag';
    import {FloatingButtonTypes} from "JS/components/types";
    import {TranslationMessages} from "lang.js";

    interface Badge {
        message: string,
        type: string
    }

    export default Vue.extend({
        name: "offer-card",
        components: {
            'badge': BadgeComponent,
            ProfileImg,
            Alert,
            Card,
            CardIconFooter,
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
            color(prefix: string = '', defaultColor: string | null = null, important = false) {
                const doReturn = (value: string | null = defaultColor) => {
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
                    scope: (<typeof store>this.$store).getters.scope.offer
                }).then(result => {
                    this.$emit('input', result);
                });
            }
        },
        computed: {
            translations(): TranslationMessages {
                return {
                    reported: this.$store.getters.trans('interface.notice.offer-reported', this.reportedTimes, {
                        times: this.reportedTimes
                    }),
                    loading: {
                        notice: this.$store.getters.trans('interface.notice.images-loading'),
                        button: this.$store.getters.trans('interface.button.images-loading'),
                    },
                    dropdown: this.$store.getters.trans('interface.label.options.additional'),
                    image: this.$store.getters.trans('interface.accessibility.offer-image'),
                }
            },
            isThisUser(): boolean {
                const user = (<typeof store>this.$store).state.user;
                return !!user && user.username === this.value.author.username;
            },
            reportedTimes(): number {
                return (<typeof store>this.$store).state.is_admin && isAdminOffer(this.value) ? this.value.reported_times : 0;
            },
            loggedIn(): boolean {
                return !!(<typeof store>this.$store).state.user;
            },
            buttons(): any {
                const ubiquitous = [
                    {
                        icon: 'shopping-cart',
                        label: this.$store.getters.trans('interface.button.buy'),
                        callback: () => {
                            if(this.isThisUser || !(<typeof store>this.$store).state.user) {
                                alert(this.$store.getters.trans('interface.notice.login-required'));
                                return;
                            }

                            if (!confirm(this.$store.getters.trans('interface.confirm.message', {
                                user: this.value.author.display_name
                            }))) {
                                return;
                            }

                            appEvents.dispatch(Events.RequestPopup, {
                                type: FloatingButtonTypes.Chat,
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
                        label: this.$store.getters.trans('interface.button.expand'),
                        callback: () => router.push(this.toOffer)
                    },
                ];

                if (this.large)
                    return ubiquitous;
                else
                    return [...ubiquitous, ...nonLarge];
            },
            imgData(): any {
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
            isAwaitingImages(): boolean {
                if (!this.value || this.value.images.length === 0)
                    return false;

                for (let img of this.value.images) {
                    if (img.ready === false)
                        return true;
                }

                return false;
            },
            shortDesc(): string {
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
            profileImage(): Image | null {
                if (this.value.author.profile_image) {
                    return this.value.author.profile_image;
                }

                return null;
            },
            toAuthor(): Location {
                return {
                    name: 'user',
                    params: {
                        username: this.value.author.username
                    }
                }
            },
            toOffer(): Location {
                return {
                    query: {
                        ...this.$route.query,
                        offer: this.value.id
                    }
                }
            },
            badges(): Badge[] | null {
                if (!this.value)
                    return null;

                let badges = [];

                switch (this.value.status) {
                    case 0:
                        badges.push({message: this.$store.getters.trans('interface.offer.draft'), type: 'warning'});
                        break;
                    case 2:
                        badges.push({message: this.$store.getters.trans('interface.offer.sold'), type: 'info'});
                        break;
                }

                if (this.value.expired)
                    badges.push({message: this.$store.getters.trans('interface.offer.expired'), type: 'danger'});

                return badges;
            },
            price(): string {
                return this.value.price ? this.value.price
                    : this.$store.getters.trans('interface.money.free');
            }
        },
        created() {
            this.$onEventListener(events, Events.OfferModified, offer => {
                this.$emit('input', offer);
            });
        }
    });
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
        min-width: 0;
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

    .offer-text-content {
        font-family: inherit;
        font-size: inherit;
        overflow: unset;
        min-width: 0;
        white-space: pre-line;
    }
</style>
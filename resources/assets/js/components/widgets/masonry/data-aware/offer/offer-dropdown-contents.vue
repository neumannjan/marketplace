<template>
    <div>
        <template v-if="!owned && !admin">
            <b-dropdown-header>{{ translations.options.additional }}</b-dropdown-header>
            <b-dropdown-item-button @click="reportOffer()">
                <icon name="flag-o" class="mr-2"/>
                {{ translations.button.report }}
            </b-dropdown-item-button>
        </template>
        <template v-else>
            <b-dropdown-header>{{ owned ? translations.options.owned : translations.options.admin }}</b-dropdown-header>
            <template v-if="!sold">
                <b-dropdown-item-button @click="editOffer()">
                    <icon name="pencil" class="mr-2"/>
                    {{ translations.button.edit }}
                </b-dropdown-item-button>
                <b-dropdown-item-button v-if="!draft && owned" :disabled="!bumpable" @click="bumpOffer()">
                    <icon name="clock-o" class="mr-2"/>
                    <template v-if="offer.bumps_left === 0">{{ translations.notice.bumpsnone }}</template>
                    <template v-else-if="offer.just_bumped">{{ translations.notice.bumpedrecently }}</template>
                    <template v-else>{{ translations.button.bump }}&#160;<small>{{ translations.button.bumptimes }}
                    </small>
                    </template>
                </b-dropdown-item-button>
            </template>
            <b-dropdown-item-button @click="removeOffer()">
                <icon name="trash-o" class="mr-2"/>
                {{ translations.button.remove }}
            </b-dropdown-item-button>
            <b-dropdown-item-button v-if="admin && reported" @click="markOfferAppropriate()">
                <icon name="check" class="mr-2"/>
                {{ translations.button.appropriate }}
            </b-dropdown-item-button>
        </template>
    </div>
</template>

<script lang="ts">
    import {Component, Prop, Vue} from 'JS/components/class-component';
    import BDropdownHeader from 'bootstrap-vue/src/components/dropdown/dropdown-header';
    import BDropdownItemButton from 'bootstrap-vue/src/components/dropdown/dropdown-item-button';

    import 'vue-awesome/icons/flag-o';
    import 'vue-awesome/icons/pencil';
    import 'vue-awesome/icons/clock-o';
    import 'vue-awesome/icons/trash-o';
    import 'vue-awesome/icons/check';

    import {ExtendedOffer, isAdminOffer, isExtendedOffer, Offer, OfferStatus} from 'JS/api/types';
    import router from 'JS/router';
    import api from 'JS/api';
    import events, {Events} from 'JS/events';
    import {Location} from 'vue-router';
    import {NotificationTypes} from 'JS/notifications';
    import {doAction} from 'JS/lib/helpers';
    import {TranslationMessages} from 'lang.js';

    @Component({
        name: "offer-dropdown-contents",
        components: {
            BDropdownHeader,
            BDropdownItemButton,
        }
    })
    export default class OfferDropdownContents extends Vue {
        @Prop({type: Object, required: true})
        offer!: Offer;

        get admin(): boolean {
            return this.$store.state.is_admin;
        }

        get owned(): boolean {
            return !!this.$store.state.user && this.$store.state.user.username === this.offer.author.username;
        }

        get sold() {
            return this.offer.status === OfferStatus.Sold;
        }

        get draft() {
            return this.offer.status === OfferStatus.Draft;
        }

        get reported() {
            return isAdminOffer(this.offer) && this.offer.reported_times > 0;
        }

        get bumpable() {
            return isExtendedOffer(this.offer) && this.offer.bumps_left > 0 && !this.offer.just_bumped;
        }

        get translations(): TranslationMessages {
            return {
                button: {
                    report: this.$store.getters.trans('interface.button.report'),
                    edit: this.$store.getters.trans('interface.button.edit'),
                    remove: this.$store.getters.trans('interface.button.remove'),
                    appropriate: this.$store.getters.trans('interface.button.mark-appropriate'),
                    bump: this.$store.getters.trans('interface.button.bump'),
                    bumptimes: this.$store.getters.trans('interface.button.bump-times', {
                        times: isExtendedOffer(this.offer) ? this.offer.bumps_left : '?'
                    }),
                },
                notice: {
                    bumpsnone: this.$store.getters.trans('interface.notice.bumps-none'),
                    bumpedrecently: this.$store.getters.trans('interface.notice.bumped-recently'),
                },
                options: {
                    additional: this.$store.getters.trans('interface.label.options.additional'),
                    owned: this.$store.getters.trans('interface.label.options.owned'),
                    admin: this.$store.getters.trans('interface.label.options.admin'),
                }
            }
        }

        removeOffer() {
            const replacements = {offer: this.offer.name};
            doAction({
                confirm: this.$store.getters.trans('interface.confirm.offer-remove', replacements),
                beforeNotification: this.$store.getters.trans('interface.notification.before.offer-remove', replacements),
                afterNotification: this.$store.getters.trans('interface.notification.after.offer-remove', replacements),
            }, () => api.requestSingle('offer-remove', {id: this.offer.id}).then(() => {
                let newRoute: Location = {};

                // if the offer modal is open, close it
                if (parseInt(this.$route.query['offer']) === this.offer.id) {
                    newRoute = {...newRoute, query: {}};
                }

                // if the offer full window is open, redirect
                if (this.$route.name === 'offer' && parseInt(this.$route.params['id']) === this.offer.id) {
                    newRoute = {...newRoute, name: 'index'};
                }

                if (newRoute.query !== undefined || newRoute.name !== undefined) {
                    // we should redirect to another route
                    router.replace(newRoute);
                }

                events.dispatch(Events.OfferRemoved, this.offer.id);
            }));
        }

        bumpOffer() {
            if (!isExtendedOffer(this.offer))
                return;

            const replacements = {offer: this.offer.name};

            doAction({
                confirm: this.$store.getters.trans('interface.confirm.offer-bump', replacements) + ' '
                + this.$store.getters.transChoice('interface.confirm.offer-bump-times', this.offer.bumps_left, {
                    times: this.offer.bumps_left,
                }),
                beforeNotification: {
                    message: this.$store.getters.trans('interface.notification.before.offer-bump', replacements),
                },
                afterNotification: {
                    message: this.$store.getters.trans('interface.notification.after.offer-bump', replacements),
                }
            }, () => api.requestSingle<Offer>('offer-bump', {id: this.offer.id}).then((offer) => {
                events.dispatch(Events.OfferModified, offer);
            }));
        }

        editOffer() {
            this.$router.push({
                name: 'offer-edit',
                params: {
                    id: this.offer.id.toString()
                }
            });
        }

        reportOffer() {
            const replacements = {offer: this.offer.name};

            doAction({
                confirm: this.$store.getters.trans('interface.confirm.offer-report', replacements),
                beforeNotification: {
                    message: this.$store.getters.trans('interface.notification.before.offer-report', replacements),
                },
                afterNotification: {
                    message: this.$store.getters.trans('interface.notification.after.offer-report', replacements),
                },
                errorNotification: {
                    type: 'warning',
                    message: `You have already reported "${this.offer.name}".`
                }
            }, () => api.requestSingle<Offer>('offer-report', {id: this.offer.id}));
        }

        markOfferAppropriate() {
            const replacements = {offer: this.offer.name};

            doAction({
                confirm: this.$store.getters.trans('interface.confirm.offer-mark-appropriate', replacements),
                beforeNotification: {
                    message: this.$store.getters.trans('interface.notification.before.offer-mark-appropriate', replacements),
                },
                afterNotification: {
                    message: this.$store.getters.trans('interface.notification.after.offer-mark-appropriate', replacements),
                }
            }, () => api.requestSingle<Offer>('offer-mark-appropriate', {id: this.offer.id}).then((offer) => {
                events.dispatch(Events.OfferModified, offer);
            }));
        }
    }
</script>
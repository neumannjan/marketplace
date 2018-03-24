<template>
    <div>
        <!-- TODO translate -->
        <template v-if="!owned">
            <b-dropdown-header>Additional options</b-dropdown-header>
            <b-dropdown-item-button>
                <icon name="flag-o" class="mr-2" />
                Report
            </b-dropdown-item-button>
        </template>
        <template v-else>
            <b-dropdown-header>Owner options</b-dropdown-header>
            <template v-if="!sold">
                <b-dropdown-item-button @click="editOffer()">
                    <icon name="pencil" class="mr-2" />
                    Edit
                </b-dropdown-item-button>
                <b-dropdown-item-button v-if="!draft" :disabled="!bumpable" @click="bumpOffer()">
                    <icon name="clock-o" class="mr-2" />
                    <template v-if="offer.bumps_left === 0">No bumps left!</template>
                    <template v-else-if="offer.just_bumped">Bumped recently</template>
                    <template v-else>Bump up as new&#160;<small>(left: {{ offer.bumps_left }})</small></template>
                </b-dropdown-item-button>
            </template>
            <b-dropdown-item-button @click="removeOffer()">
                <icon name="trash-o" class="mr-2" />
                Remove
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

    import {Offer, OfferStatus} from 'JS/api/types';
    import store from 'JS/store';
    import router from 'JS/router';
    import api from 'JS/api';
    import events, {Events} from 'JS/events';
    import {Location} from 'vue-router';
    import notifications, {NotificationTypes} from 'JS/notifications';

    interface NotificationPayload {
        id: NotificationTypes,
        message: string
    }

    interface ActionPayload {
        confirm?: string,
        beforeNotification?: NotificationPayload,
        afterNotification?: NotificationPayload,
    }

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

        get owned(): boolean {
            return !!store.state.user && store.state.user.username === this.offer.author.username;
        }

        get sold() {
            return this.offer.status === OfferStatus.Sold;
        }

        get draft() {
            return this.offer.status === OfferStatus.Draft;
        }

        get bumpable() {
            return this.offer.bumps_left > 0 && !this.offer.just_bumped;
        }

        /**
         * Does a basic action that may require confirmation and may display notifications
         *
         */
        private doAction(payload: ActionPayload, func: () => Promise<any>) {
            if (!payload.confirm || confirm(payload.confirm)) {
                if (payload.beforeNotification) {
                    notifications.showNotification({
                        id: payload.beforeNotification.id,
                        type: 'info',
                        message: payload.beforeNotification.message,
                        persistent: true
                    });
                }

                func().then(() => {
                    if (payload.beforeNotification) {
                        notifications.hideNotification(payload.beforeNotification.id);
                    }

                    if (payload.afterNotification) {
                        notifications.showNotification({
                            id: payload.afterNotification.id,
                            type: 'success',
                            message: payload.afterNotification.message,
                            persistent: false
                        });
                    }
                });
            }
        }

        removeOffer() {
            // TODO translate
            this.doAction({
                confirm: `You are trying to remove "${this.offer.name}". Are you sure you want to continue?`,
                beforeNotification: {
                    id: NotificationTypes.RemovingOffer,
                    message: `"${this.offer.name}" is being removed.`,
                },
                afterNotification: {
                    id: NotificationTypes.RemovedOffer,
                    message: `"${this.offer.name}" was successfully removed.`,
                }
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
            //TODO translate
            this.doAction({
                confirm: `Are you sure you want to make "${this.offer.name}" reappear on top as a new offer? `
                    + `You can do this only ${this.offer.bumps_left} times!`,
                beforeNotification: {
                    id: NotificationTypes.BumpingOffer,
                    message: `Bumping "${this.offer.name}".`
                },
                afterNotification: {
                    id: NotificationTypes.BumpedOffer,
                    message: `"${this.offer.name}" has been bumped.`
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
    }
</script>
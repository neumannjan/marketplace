<template>
    <div>
        <!-- TODO translate -->
        <template v-if="!owned && !admin">
            <b-dropdown-header>Additional options</b-dropdown-header>
            <b-dropdown-item-button @click="reportOffer()">
                <icon name="flag-o" class="mr-2" />
                Report
            </b-dropdown-item-button>
        </template>
        <template v-else>
            <b-dropdown-header>{{ owned ? 'Owner options' : 'Administrator options' }}</b-dropdown-header>
            <template v-if="!sold">
                <b-dropdown-item-button @click="editOffer()">
                    <icon name="pencil" class="mr-2" />
                    Edit
                </b-dropdown-item-button>
                <b-dropdown-item-button v-if="!draft && owned" :disabled="!bumpable" @click="bumpOffer()">
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
            <b-dropdown-item-button v-if="admin && reported" @click="markOfferAppropriate()">
                <icon name="check" class="mr-2" />
                Mark as appropriate
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

    import {ExtendedOffer, isExtendedOffer, Offer, OfferStatus, isAdminOffer} from 'JS/api/types';
    import store from 'JS/store';
    import router from 'JS/router';
    import api from 'JS/api';
    import events, {Events} from 'JS/events';
    import {Location} from 'vue-router';
    import {NotificationTypes} from 'JS/notifications';
    import {doAction} from 'JS/lib/helpers';

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

        removeOffer() {
            // TODO translate
            doAction({
                confirm: `You are trying to remove "${this.offer.name}". Are you sure you want to continue?`,
                beforeNotification: `"${this.offer.name}" is being removed.`,
                afterNotification: `"${this.offer.name}" was successfully removed.`
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

            //TODO translate
            doAction({
                confirm: `Are you sure you want to make "${this.offer.name}" reappear on top as a new offer? `
                    + `You can do this only ${this.offer.bumps_left} times!`,
                beforeNotification: {
                    message: `Bumping "${this.offer.name}".`
                },
                afterNotification: {
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

        reportOffer() {
            //TODO translate            
            doAction({
                confirm: `Are you sure you want to report "${this.offer.name}" as inappropriate?`,
                beforeNotification: {
                    message: `Reporting "${this.offer.name}".`
                },
                afterNotification: {
                    message: `"${this.offer.name}" has been reported.`
                },
                errorNotification: {
                    type: 'warning',
                    message: `You have already reported "${this.offer.name}".`
                }
            }, () => api.requestSingle<Offer>('offer-report', {id: this.offer.id}));
        }

        markOfferAppropriate() {
            //TODO translate            
            doAction({
                confirm: `Are you sure you want to mark "${this.offer.name}" as appropriate?`,
                beforeNotification: {
                    message: `Marking "${this.offer.name}" as appropriate.`
                },
                afterNotification: {
                    message: `"${this.offer.name}" has been marked as appropriate.`
                }
            }, () => api.requestSingle<Offer>('offer-mark-appropriate', {id: this.offer.id}).then((offer) => {
                events.dispatch(Events.OfferModified, offer);
            }));
        }
    }
</script>
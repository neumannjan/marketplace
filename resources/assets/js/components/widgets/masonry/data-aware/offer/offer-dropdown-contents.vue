<template>
    <div>
        <!-- TODO translate -->
        <template v-if="!owned">
            <b-dropdown-header>Additional options</b-dropdown-header>
            <b-dropdown-item-button><icon name="flag-o" class="mr-2" />Report</b-dropdown-item-button>
        </template>
        <template v-else>
            <b-dropdown-header>Owner options</b-dropdown-header>
            <b-dropdown-item-button><icon name="pencil" class="mr-2" />Edit</b-dropdown-item-button>
            <b-dropdown-item-button><icon name="clock-o" class="mr-2" />Bump up as new</b-dropdown-item-button>
            <b-dropdown-item-button @click="removeOffer()"><icon name="trash-o" class="mr-2" />Remove</b-dropdown-item-button>
        </template>
    </div>
</template>

<script lang="ts">
    import { Component, Vue, Prop } from 'vue-property-decorator';
    import BDropdownHeader from 'bootstrap-vue/src/components/dropdown/dropdown-header';
    import BDropdownItemButton from 'bootstrap-vue/src/components/dropdown/dropdown-item-button';

    import 'vue-awesome/icons/flag-o';
    import 'vue-awesome/icons/pencil';
    import 'vue-awesome/icons/clock-o';
    import 'vue-awesome/icons/trash-o';
    
    import { Offer } from 'JS/api/types';
    import store from 'JS/store';
    import router from 'JS/router';
    import api from 'JS/api';
    import events,{ Events } from 'JS/events';
    import { Location } from 'vue-router';

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

        removeOffer() {
            // TODO translate
            if(confirm(`You are trying to remove "${this.offer.name}". Are you sure you want to continue?`)) {
                api.requestSingle('offer-remove', {id: this.offer.id})
                    .then(() => {
                        let newRoute: Location = {};

                        // if the offer modal is open, close it
                        if(parseInt(this.$route.query['offer']) === this.offer.id) {
                            newRoute = {...newRoute, query: {}};
                        }
                        
                        // if the offer full window is open, redirect
                        if(this.$route.name === 'offer' && parseInt(this.$route.params['id']) === this.offer.id) {
                            newRoute = {...newRoute, name: 'index'};
                        } 
                        
                        if (newRoute.query !== undefined || newRoute.name !== undefined) {
                            // we should redirect to another route
                            router.replace(newRoute);
                        }
                        
                        if(newRoute.name === undefined) {
                            // we should just refresh current route
                            events.dispatch(Events.AppRefresh, undefined);
                        }
                    });
            }
        }
    }
</script>
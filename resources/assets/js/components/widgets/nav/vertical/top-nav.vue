<template>
    <base-nav :items="items"/>
</template>

<script lang="ts">
    import NavComponent from '../nav.vue';
    import store from 'JS/store';
    import Vue from 'vue';

    import 'vue-awesome/icons/home';
    import 'vue-awesome/icons/search';
    import 'vue-awesome/icons/user';
    import 'vue-awesome/icons/flag';

    export default Vue.extend({
        name: 'top-nav',
        components: {
            'base-nav': NavComponent
        },
        computed: {
            items(): any[] {
                const items = [
                    {
                        label: 'Dashboard',
                        icon: 'home',
                        route: 'index'
                    },
                    {
                        label: 'Search',
                        icon: 'search',
                        route: 'search',
                        activeAnyParams: true,
                    }
                ];

                const user = (<typeof store> this.$store).state.user;

                const authItems = !!user ? [
                    {
                        label: 'Profile',
                        icon: 'user',
                        route: 'user',
                        params: {
                            username: user.username
                        }
                    }
                ] : [];

                const adminItems = (<typeof store>this.$store).state.is_admin ? [
                    {
                        label: 'Administration',
                        icon: 'flag',
                        route: 'admin',
                        activeAnyParams: true,
                    }
                ] : [];

                return [...items, ...authItems, ...adminItems];
            }
        }
    });
</script>
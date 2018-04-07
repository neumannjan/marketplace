<template>
    <base-nav :items="items"/>
</template>

<script>
    import NavComponent from '../nav.vue';
    import store from 'JS/store';

    import 'vue-awesome/icons/sign-in';
    import 'vue-awesome/icons/user-plus';
    import 'vue-awesome/icons/sign-out';
    import 'vue-awesome/icons/cog';

    export default {
        name: 'bottom-nav',
        components: {
            'base-nav': NavComponent
        },
        computed: {
            items() {
                const guestItems = [
                    {
                        label: this.$store.getters.trans('interface.page.login'),
                        icon: 'sign-in',
                        route: 'login'
                    },
                    {
                        label: this.$store.getters.trans('interface.page.register'),
                        icon: 'user-plus',
                        route: 'register'
                    },
                ];

                const authItems = [
                    {
                        label: this.$store.getters.trans('interface.page.user-settings'),
                        icon: 'cog',
                        route: 'user-settings'
                    },
                    {
                        label: this.$store.getters.trans('interface.page.logout'),
                        icon: 'sign-out',
                        id: 'logout',
                        callback: () => store.dispatch('logout')
                    },
                ];

                return this.$store.state.user ? authItems : guestItems;
            }
        }
    };
</script>
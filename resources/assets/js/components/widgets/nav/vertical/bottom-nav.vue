<template>
    <base-nav :items="items"/>
</template>

<script>
    import NavComponent from '../nav.vue';
    import {mapState} from 'vuex';
    import store from 'JS/store';

    import 'vue-awesome/icons/sign-in';
    import 'vue-awesome/icons/user-plus';
    import 'vue-awesome/icons/sign-out';

    const guestItems = [
        {
            label: 'Log in',
            icon: 'sign-in',
            route: 'login'
        },
        {
            label: 'Register',
            icon: 'user-plus',
            route: 'register'
        },
    ];

    const authItems = [
        {
            label: 'Log out',
            icon: 'sign-out',
            id: 'logout',
            callback: () => store.dispatch('logout')
        },
    ];

    export default {
        name: 'bottom-nav',
        components: {
            'base-nav': NavComponent
        },
        computed: {
            ...mapState({
                items: state => state.is_authenticated ? authItems : guestItems
            })
        }
    };
</script>
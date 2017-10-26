<template>
    <base-nav :items="items"></base-nav>
</template>

<script>
    import NavComponent from './nav.vue';
    import {mapState} from 'vuex';
    import store from '../../store/store';
    import Api from '../../api';

    let guestItems = [
        {
            label: 'Log in',
            icon: 'fa fa-sign-in',
            route: 'login'
        },
        {
            label: 'Register',
            icon: 'fa fa-user-plus',
            route: 'register'
        },
    ];

    let authItems = [
        {
            label: 'Log out',
            icon: 'fa fa-sign-out',
            callback: () => store.dispatch('logout')
        },
    ];

    export default {
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
<template>
    <base-nav :items="items"/>
</template>

<script>
    import NavComponent from '../nav.vue';
    import {mapState} from 'vuex';

    import 'vue-awesome/icons/home';
    import 'vue-awesome/icons/cog';
    import 'vue-awesome/icons/user';

    const items = [
        {
            label: 'Dashboard',
            icon: 'home',
            route: 'index'
        },
        {
            label: 'Test',
            icon: 'cog',
            route: 'test'
        }
    ];

    const authItems = [
        {
            label: 'Admin',
            icon: 'user',
            route: 'me'
        }
    ];

    const adminItems = [];

    export default {
        name: 'top-nav',
        components: {
            'base-nav': NavComponent
        },
        computed: {
            ...mapState({
                items: state => {
                    const i1 = items;
                    const i2 = state.is_authenticated ? authItems : [];
                    const i3 = state.is_admin ? adminItems : [];

                    return [...i1, ...i2, ...i3];
                }
            })
        }
    };
</script>
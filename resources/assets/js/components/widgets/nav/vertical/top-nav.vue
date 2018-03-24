<template>
    <base-nav :items="items"/>
</template>

<script>
    import NavComponent from '../nav.vue';
    import {mapState} from 'vuex';

    import 'vue-awesome/icons/home';
    import 'vue-awesome/icons/search';
    import 'vue-awesome/icons/user';

    export default {
        name: 'top-nav',
        components: {
            'base-nav': NavComponent
        },
        computed: {
            ...mapState({
                items: state => {
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

                    const authItems = !!state.user ? [
                        {
                            label: 'Admin',
                            icon: 'user',
                            route: 'user',
                            params: {
                                username: state.user.username
                            }
                        }
                    ] : [];

                    const adminItems = state.is_admin ? [] : [];

                    return [...items, ...authItems, ...adminItems];
                }
            })
        }
    };
</script>
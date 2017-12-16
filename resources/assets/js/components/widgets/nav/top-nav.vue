<template>
    <base-nav :items="items"></base-nav>
</template>

<script>
    import NavComponent from './nav.vue';

    import 'vue-awesome/icons/home';
    import 'vue-awesome/icons/cog';

    let items = [
        {
            label: 'Dashboard',
            icon: 'home',
            route: 'index'
        },
        {
            label: 'Admin',
            icon: 'cog',
            route: 'user',
            params: {
                name: 'admin'
            }
        }
    ];

    export default {
        components: {
            'base-nav': NavComponent
        },
        data: () => ({
            items: items
        })
    };
</script>
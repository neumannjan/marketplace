<template>
    <base-nav :items="items"></base-nav>
</template>

<script>
    import NavComponent from './nav.vue';

    let items = [
        {
            label: 'Dashboard',
            icon: 'fa fa-home',
            route: 'index'
        },
        {
            label: 'Admin',
            icon: 'fa fa-cog',
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
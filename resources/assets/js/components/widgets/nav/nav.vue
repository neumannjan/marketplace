<template>
    <ul>
        <nav-item v-for="item in items" v-bind="item" :key="item.label"></nav-item>
    </ul>
</template>

<script>
    import NavItemComponent from './nav-item.vue';

    export default {
        props: ['items'],
        components: {
            'nav-item': NavItemComponent
        },
    };
</script>
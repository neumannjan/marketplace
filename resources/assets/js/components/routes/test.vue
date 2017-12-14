<template>
    <h1>{{ title }}</h1>
</template>

<script>
    import route from '../mixins/route';

    export default {
        props: ['name'],
        mixins: [route],
        computed: {
            title() {
                return this.name + " page";
            }
        }
    };
</script>
<template>
    <h1>{{ title }}</h1>
</template>

<script>
    import route from '../mixins/route';

    export default {
        mixins: [route],
        computed: {
            title() {
                return "Test page";
            }
        }
    };
</script>
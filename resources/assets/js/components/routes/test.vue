<template>
    <h1>{{ name }} page</h1>
</template>

<script>
    import title from './../mixins/title';

    export default {
        props: ['name'],
        mixins: [title],
        computed: {
            title() {
                return this.name + " page";
            }
        }
    };
</script>
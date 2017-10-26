<template>
    <h1>Index page</h1>
</template>

<script>
    import title from './../mixins/title';

    export default {
        mixins: [title],
        data: () => ({
            title: "Index page"
        })
    };
</script>
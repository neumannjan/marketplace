<template>
    <div>
        <h1>{{ title }}</h1>
    </div>
</template>

<script>
    import route from '../mixins/route';

    export default {
        mixins: [route],
        data: () => ({
            title: "404"
        })
    };
</script>
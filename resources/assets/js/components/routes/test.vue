<template>
    <h1>{{ title }}</h1>
</template>

<script>
    import route from 'JS/components/mixins/route';

    export default {
        mixins: [route],
        computed: {
            title() {
                return "Test page";
            }
        },
        data: () => ({
            isTopLevelRoute: true
        }),
    };
</script>
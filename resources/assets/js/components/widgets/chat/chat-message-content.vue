<template>
    <!-- TODO -->
    <p :is="as">{{ message.content }}</p>
</template>

<script>
    export default {
        name: "chat-message-content",
        props: {
            message: {
                type: Object,
                required: true
            },
            as: {
                default: 'p'
            }
        }
    }
</script>

<style scoped>

</style>
<template>
    <span :class="['badge', `badge-${type}`]"><slot>{{ message }}</slot></span>
</template>

<script>
    export default {
        name: "badge",
        props: {
            type: {
                type: String,
                default: 'primary'
            },
            message: {
                type: String
            }
        }
    }
</script>
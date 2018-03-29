<template functional>
    <span :class="['badge', `badge-${props.type}`]"><slot>{{ props.message }}</slot></span>
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
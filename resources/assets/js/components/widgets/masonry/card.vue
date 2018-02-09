<template>
    <div class="card">
        <div v-if="$slots.header" :class="['card-header', headerClass]">
            <slot name="header"/>
        </div>
        <slot name="post-header"/>
        <div class="card-body">
            <slot/>
        </div>
        <slot name="pre-footer"/>
        <div v-if="$slots.footer" :class="['card-footer', footerClass]">
            <slot name="footer"/>
        </div>
    </div>
</template>

<script>
    export default {
        name: "card",
        props: {
            headerClass: String,
            footerClass: String,
        }
    }
</script>
<template>
    <div>
        <modal v-for="(modal, key) in data"
               v-if="$route.query[key]"
               :key="key"
               :size="modal.size"
               @close="close(key)">
            <component :is="modal.component" v-bind="{[key]: $route.query[key]}" @close="close(key)"/>
        </modal>
    </div>
</template>

<script>
    import Modal from "JS/components/widgets/modal";
    import router from 'JS/router';

    export default {
        components: {Modal},
        name: "modal-router",
        props: {
            data: {
                type: Object,
                required: true,
            }
        },
        methods: {
            close(key) {
                if (this.$store.state.reRoutedTimes > 0) {
                    router.back();
                } else {
                    router.push({query: {}});
                }
            }
        }
    }
</script>
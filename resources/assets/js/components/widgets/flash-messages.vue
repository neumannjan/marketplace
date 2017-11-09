<template>
    <div>
        <alert v-for="flash in flashMessages" :key="flash.key" @close="remove(flash)" :type="flash.type">{{ flash.message }}</alert>
    </div>
</template>

<script>
    import AlertComponent from './alert.vue';

    export default {
        components: {
            alert: AlertComponent
        },
        computed: {
            flashMessages() {
                let messages = [];
                for([type, flashesOfType] of Object.entries(this.$store.state.flash)) {
                    for([key, message] of Object.entries(flashesOfType)) {
                        messages.push({
                            type: type,
                            key: key,
                            message: message
                        });
                    }
                }

                return messages;
            }
        },
        methods: {
            remove(flash) {
                this.$store.commit('removeFlash', flash);
            }
        }
    }

</script>
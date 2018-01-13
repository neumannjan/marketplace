<template>
    <div>
        <alert v-for="(flash, key) in messages" :key="key" @close="remove(key)" :type="flash.type">{{ flash.message }}
        </alert>
    </div>
</template>

<script>
    import AlertComponent from './alert.vue';

    export default {
        name: 'flash-messages',
        components: {
            alert: AlertComponent
        },
        computed: {
            messages() {
                return this.$store.state.flash;
            }
        },
        methods: {
            remove(key) {
                this.$store.commit('removeFlash', key);
            }
        }
    }

</script>
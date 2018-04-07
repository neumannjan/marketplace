<template>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ translations.notifications }}</h5>
        </div>
    </div>
</template>

<script lang="ts">
    import {Component, Vue} from "JS/components/class-component";
    import {TranslationMessages} from "lang.js";

    @Component({
        name: 'notifications-card'
    })
    export default class NotificationsCard extends Vue {
        get translations(): TranslationMessages {
            return {
                notifications: this.$store.getters.trans('interface.page.notifications'),
            }
        }
    }
</script>

<style scoped>

</style>
<template>
    <div>
        <router-view/>
    </div>
</template>

<script lang="ts">
    import { Vue, Component, mixins } from "JS/components/class-component";
    import route from "JS/components/mixins/route";

    @Component({
        name: 'admin-route'
    })
    export default class AdminRoute extends mixins(route) {
        readonly isTopLevelRoute: boolean = true;
        get title(): string {
            return "Administration";
        }
    }
</script>

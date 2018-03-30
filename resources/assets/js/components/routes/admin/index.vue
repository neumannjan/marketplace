<template>
    <div>
        <router-view/>
    </div>
</template>

<script lang="ts">
    import { Vue, Component, mixins } from "JS/components/class-component";
    import route from "JS/components/mixins/route";
    import routeGuard from "JS/components/mixins/route-guard";
    import store from "JS/store";

    @Component({
        name: 'admin-route'
    })
    export default class AdminRoute extends mixins(route, routeGuard('admin', () => store.state.is_admin)) {
        readonly isTopLevelRoute: boolean = true;
        get title(): string {
            return "Administration";
        }
    }
</script>

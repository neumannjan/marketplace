<template>
    <li :class="['nav-item', {active: this.active}]">
        <router-link v-if="isToRoute" class="nav-link" :to="routeDefinition" :aria-label="labelFull">
            <icon v-if="icon" :name="icon" :label="label" :scale="1.125"/>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;(current)</span> <!-- TODO translate the "current" word -->
            </template>
        </router-link>
        <a v-else="" href="#" class="nav-link" @click.prevent="callback">
            <icon v-if="icon" :name="icon" :label="label" :scale="1.125"/>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;(current)</span> <!-- TODO translate the "current" word -->
            </template>
        </a>
    </li>
</template>

<script>
    import router from 'JS/router';

    export default {
        props: {
            route: {
                type: String
            },
            path: {
                type: String
            },
            params: {
                type: Object
            },
            label: {
                type: String,
                required: true
            },
            icon: {
                type: String
            },
            callback: {}
        },
        computed: {
            active() {
                return router.routesMatch(this.routeDefinition, this.$route);
            },
            labelFull() {
                return this.label + (this.active ? ' (current)' : ''); // TODO translate the "current" word
            },
            routeDefinition() {
                if (this.route) {
                    return {name: this.route, params: this.params};
                } else {
                    return {path: this.path};
                }
            },
            isToRoute() {
                return this.route || this.path;
            }
        },
    };
</script>
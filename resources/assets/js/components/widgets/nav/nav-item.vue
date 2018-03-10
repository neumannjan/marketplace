<template>
    <li :class="['nav-item', {active: this.active}]">
        <router-link v-if="isToRoute" class="nav-link" :to="routeDefinition" :aria-label="labelFull">
            <icon v-if="icon" :name="icon" :label="label" :scale="1.125"/>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;(current)</span> <!-- TODO translate the "current" word -->
            </template>
        </router-link>
        <a v-else href="#" class="nav-link" @click.prevent="callback">
            <icon v-if="icon" :name="icon" :label="label" :scale="1.125"/>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;(current)</span> <!-- TODO translate the "current" word -->
            </template>
        </a>
    </li>
</template>

<script>
    import router,{ routesMatch } from 'JS/router';
    import Vue from 'vue';
    import { Location } from 'vue-router/types/router';

    export default Vue.extend({
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
            activeAnyParams: {
                type: Boolean,
                default: false
            },
            callback: {}
        },
        computed: {
            active() {
                //@ts-ignore
                return routesMatch(this.routeDefinition, this.$route, this.activeAnyParams);
            },
            labelFull() {
                return this.label + (this.active ? ' (current)' : ''); // TODO translate the "current" word
            },
            /**
             * @returns {Location}
             */
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
    });
</script>
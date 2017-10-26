<template>
    <li :class="['nav-item', {active: this.active}]">
        <router-link class="nav-link" :to="route" :aria-label="labelFull">
            <i v-if="icon" :class="[icon]" aria-hidden="true"></i>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;(current)</span> <!-- TODO translate the "current" word -->
            </template>
        </router-link>
    </li>
</template>

<script>
    export default {
        props: {
            name: {
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
            }
        },
        computed: {
            active() {
                return this.name === this.$route.name || this.path === this.$route.path;
            },
            labelFull() {
                return this.label + (this.active ? ' (current)' : ''); // TODO translate the "current" word
            },
            route() {
                if (this.name) {
                    return {name: this.name, params: this.params};
                } else {
                    return {path: this.path};
                }
            }
        },
    };
</script>
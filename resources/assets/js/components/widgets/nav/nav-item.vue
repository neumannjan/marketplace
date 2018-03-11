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

<script lang="ts">
    import { Prop, Vue } from "vue-property-decorator";
    import Component from 'vue-class-component';
    import router,{ routesMatch } from 'JS/router';
    import { Location } from 'vue-router/types/router';

    @Component({
        name: 'nav-item'
    })
    export default class NavItem extends Vue {
        @Prop({type: String})
        route: string | undefined;

        @Prop({type: String})
        path: string | undefined;

        @Prop({type: Object})
        params: {[index: string]: string} | undefined;

        @Prop({type: String, required: true})
        label!: string | undefined;

        @Prop({type: String})
        icon: string | undefined;

        @Prop({type: Boolean, default: false})
        activeAnyParams!: boolean | undefined;

        @Prop({})
        callback: ((e: MouseEvent) => void) | undefined;

        get active(): boolean {
            return routesMatch(this.routeDefinition, this.$route, this.activeAnyParams);
        }

        get labelFull(): string {
            return this.label + (this.active ? ' (current)' : ''); // TODO translate the "current" word
        }

        get routeDefinition(): Location {
            if (this.route) {
                return {name: this.route, params: this.params};
            } else {
                return {path: this.path};
            }
        }

        get isToRoute(): boolean {
            return !!this.route || !!this.path;
        }
    }
</script>
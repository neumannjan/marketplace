<template>
    <li :class="['nav-item', {active: this.active}]">
        <router-link v-if="isToRoute" class="nav-link" :to="routeDefinition" :title="labelFull">
            <icon v-if="icon" :name="icon" :label="label" :scale="1.125"/>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;{{ translations.current }}</span>
            </template>
        </router-link>
        <a v-else href="#" class="nav-link" @click.prevent="callback" :title="label">
            <icon v-if="icon" :name="icon" :label="label" :scale="1.125"/>
            <template v-else>
                {{ label }}
                <span class="sr-only">&nbsp;{{ translations.current }}</span>
            </template>
        </a>
    </li>
</template>

<script lang="ts">
    import Component, {Prop, Vue} from "JS/components/class-component";
    import {routesMatch} from 'JS/router';
    import {Location} from 'vue-router/types/router';
    import {TranslationMessages} from "lang.js";

    @Component({
        name: 'nav-item'
    })
    export default class NavItem extends Vue {
        @Prop({type: String})
        route: string | undefined;

        @Prop({type: String})
        path: string | undefined;

        @Prop({type: Object})
        params: { [index: string]: string } | undefined;

        @Prop({type: String, required: true})
        label!: string;

        @Prop({type: String})
        icon: string | undefined;

        @Prop({type: Boolean, default: false})
        activeAnyParams!: boolean;

        @Prop({})
        callback: ((e: MouseEvent) => void) | undefined;

        get translations(): TranslationMessages {
            return {
                current: this.$store.getters.trans('interface.label.page-current'),
            }
        }

        get active(): boolean {
            return routesMatch(this.routeDefinition, this.$route, this.activeAnyParams, true);
        }

        get labelFull(): string {
            return this.label + (this.active ? ` ${this.translations.current}` : '');
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
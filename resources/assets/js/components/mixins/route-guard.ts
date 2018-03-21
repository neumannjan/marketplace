import { Vue, Component, Watch } from "vue-property-decorator";
import { Location, Route, NavigationGuard, RawLocation } from "vue-router";
import router from "JS/router";

export default function<V extends Vue>(name: string, get: (vm: V) => boolean, goTo: Location = {name: 'index'}) {
    name = `guard_${name}`;

    @Component({})
    class RouteGuardMixin extends Vue {
        @Watch(name)
        onGuardValueChange(val: boolean) {
            if(val !== true) {
                router.push(goTo);
            }
        }

        get [name](): boolean {
            return get(<any>this);
        }

        beforeRouteEnter(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
            next((vm: any) => {
                if (vm[name] && vm[name] !== true)
                    router.push(goTo);
            });
        }

        beforeRouteUpdate(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
            if ((<any>this)[name] === true)
                next();
            else
                next(goTo);
        }
    }

    return RouteGuardMixin;
};
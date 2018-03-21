import {routeEvents, RouteEvents, routesMatch} from 'JS/router';
import Vue from "vue";
import Component from 'JS/components/class-component';
import { Route, NavigationGuard, RawLocation } from 'vue-router';
import { events, Events } from 'JS/events';

type Result = {
    [key: string]: any
};

export interface RouteFetchMixinInterface {
    doFetch(): void;
}

export default function <R extends Result, P extends object>
(fetchAsyncFunction: (params: P) => Promise<R | null>, nullObj: R, before: boolean = true) {

    async function handleResult(vm: Vue, result: R) {
        function setValues(from: R) {
            for (let [key, value] of Object.entries(from))
                vm.$data[key] = value;
        }

        setValues(nullObj);
        await vm.$nextTick();

        if (result !== undefined) {
            setValues(result);
            await vm.$nextTick();
        }

        routeEvents.dispatch(RouteEvents.Loaded, undefined);
    }

    function notifyLoading() {
        if (before)
            routeEvents.dispatch(RouteEvents.Loading, undefined);
    }

    @Component({})
    class RouteFetchMixin extends Vue implements RouteFetchMixinInterface {
        beforeRouteEnter(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: RouteFetchMixin & Vue) => any) | void) => void) {
            if (before) {
                notifyLoading();
                fetchAsyncFunction(<any>to.params).then(result => {
                    next((vm: RouteFetchMixin & Vue) => {
                        handleResult(vm, result ? result : nullObj);
                    });
                });
            } else {
                next();
            }
        }

        beforeRouteUpdate(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: RouteFetchMixin & Vue) => any) | void) => void) {
            if (routesMatch(to, from)) {
                next();
                return;
            }

            notifyLoading();
            fetchAsyncFunction(<any>to.params).then(result => {
                if (!before) next();
                handleResult(this, result ? result : nullObj);
                if (before) next();
            });
        }

        created() {
            let fetchLater = false;

            if (before) {
                for (let matched of this.$route.matched) {
                    for (let instance of Object.values(matched.instances)) {
                        if (instance === this) {
                            fetchLater = true;
                            break;
                        }
                    }
                }
            }

            if (!fetchLater) {
                this.doFetch();
            }

            this.$onEventListener(events, Events.AfterAppRefresh, () => {
                this.doFetch();
            });
        }

        doFetch() {
            notifyLoading();
            fetchAsyncFunction(<any>this).then(result => handleResult(this, result ? result : nullObj));
        }
    }

    return RouteFetchMixin;
}
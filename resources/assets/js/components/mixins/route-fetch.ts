import {routeEvents, RouteEvents, routesMatch} from 'JS/router';
import {Component, Vue} from 'JS/components/class-component';
import {RawLocation, Route} from 'vue-router';
import {events, Events} from 'JS/events';

type Result = {
    [key: string]: any
};

export interface RouteFetchMixinInterface {
    $lastRouteFetch: Route | null;

    doFetch(): void;
}

function get<R>(from: R | (() => R)) {
    if (typeof from === 'function')
        return from();
    else
        return from;
}

export default function <R extends Result, P extends object>
(fetchAsyncFunction: (params: P) => Promise<R | null>, nullObj: R | (() => R), before: boolean = true, after?: (vm: Vue) => void) {

    const _nullObj = get(nullObj);

    async function handleResult(vm: Vue, result: R) {
        function setValues(from: R) {
            for (let [key, value] of Object.entries(from))
                vm.$data[key] = value;
        }

        setValues(_nullObj);
        await vm.$nextTick();

        setValues(result);
        if (after) {
            after(vm);
        }
        await vm.$nextTick();

        routeEvents.dispatch(RouteEvents.Loaded, undefined);
    }

    function notifyLoading() {
        if (before)
            routeEvents.dispatch(RouteEvents.Loading, undefined);
    }

    @Component({})
    class RouteFetchMixin extends Vue implements RouteFetchMixinInterface {
        $lastRouteFetch: Route | null = null;

        beforeRouteEnter(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: RouteFetchMixin & Vue) => any) | void) => void) {
            if (before) {
                notifyLoading();
                fetchAsyncFunction(<any>to.params).then(result => {
                    next((vm: RouteFetchMixin & Vue) => {
                        handleResult(vm, result ? result : _nullObj);
                    });
                });
            } else {
                next((vm: RouteFetchMixin & Vue) => {
                    vm.doFetch();
                });
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
                handleResult(this, result ? result : _nullObj);
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
            if (!this.$lastRouteFetch || !routesMatch(this.$lastRouteFetch, this.$route)) {
                this.$lastRouteFetch = this.$route;
                notifyLoading();
                fetchAsyncFunction(<any>this).then(result => handleResult(this, result ? result : _nullObj));
            }
        }
    }

    return RouteFetchMixin;
}
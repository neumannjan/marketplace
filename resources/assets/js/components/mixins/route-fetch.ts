import {routeEvents, RouteEvents, routesMatch} from 'JS/router';
import Vue from "vue";

type Result = {
    [key: string]: any
};

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

        routeEvents.dispatch(RouteEvents.Loaded);
    }

    function notifyLoading() {
        if (before)
            routeEvents.dispatch(RouteEvents.Loading);
    }

    return Vue.extend({
        beforeRouteEnter(to, from, next) {
            if (before) {
                notifyLoading();
                fetchAsyncFunction(<any>to.params).then(result => {
                    next(vm => {
                        handleResult(vm, result ? result : nullObj);
                    });
                });
            } else {
                next();
            }
        },
        beforeRouteUpdate(to, from, next) {
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
        },
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
                notifyLoading();
                this.doFetch();
            }
        },
        methods: {
            doFetch() {
                fetchAsyncFunction(<any>this).then(result => handleResult(this, result ? result : nullObj));
            }
        }
    });
}
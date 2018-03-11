import Vue from "vue";
import { Location, Route, NavigationGuard } from "vue-router";
import router from "JS/router";

export default function<V extends Vue>(name: string, get: (vm: V) => boolean, goTo: Location = {name: 'index'}) {
    name = `guard_${name}`;

    return Vue.extend({
        watch: {
            [name](val: boolean) {
                if (val !== true) {
                    router.push(goTo);
                }
            }
        },
        beforeRouteEnter(to, from, next) {
            next((vm: any) => {
                if (vm[name] && vm[name] !== true)
                    router.push(goTo);
            });
        },

        beforeRouteUpdate(to, from, next) {
            if (this[name] === true)
                next();
            else
                next(goTo);
        },
        computed: {
            [name](): boolean {
                return get(<any>this);
            }
        }
    });
};
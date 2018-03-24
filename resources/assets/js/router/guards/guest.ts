import store from "JS/store";
import NavigationGuard from "JS/router/navigation-guard";

export default {
    beforeEnter(to, from, next) {
        if (!store.state.user) {
            next();
        } else {
            next({name: 'index'});
        }
    }
} as NavigationGuard;
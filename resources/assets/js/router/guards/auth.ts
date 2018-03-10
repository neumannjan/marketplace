import store from "JS/store";
import NavigationGuard from "JS/router/navigation-guard";

export default {
    beforeEnter(to, from, next) {
        if (store.state.is_authenticated) {
            next();
        } else {
            next({name: 'login'});
        }
    }
} as NavigationGuard;
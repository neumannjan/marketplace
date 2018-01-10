import store from "JS/store";

export default {
    beforeEnter: (to, from, next) => {
        if (store.state.is_authenticated) {
            next();
        } else {
            next({name: 'login'});
        }
    }
}
import store from "JS/store";
import router from "JS/router";
import {mapState} from 'vuex';

const beforeEnter = (to, from, next) => {
    if (store.state.is_authenticated) {
        next();
    } else {
        next({name: 'login'});
    }
};

export default {
    beforeEnter: beforeEnter,
    beforeRouteEnter: beforeEnter,
    computed: {
        ...mapState({
            $__routeIsAuthenticated: state => state.is_authenticated
        })
    },
    watch: {
        $__routeIsAuthenticated(isAuth) {
            if (!isAuth) {
                router.push({name: 'index'});
            }
        }
    }
}
import Vue from 'vue';
import Vuelidate from 'vuelidate';
import IconComponent from 'vue-awesome/components/Icon';
import ElementQueries from 'css-element-queries/src/ElementQueries';
import stickyfill from 'stickyfilljs';

import store from 'JS/store';
import echo from 'JS/echo';
import router from 'JS/router';
import AppComponent from './components/app.vue';
import LazyImgComponent from './components/widgets/image/lazy-img';
import api from "JS/api";

// setup

Vue.use(Vuelidate);
ElementQueries.listen();

// Global Vue components and directives

Vue.component('icon', IconComponent);
Vue.component('lazy-img', LazyImgComponent);

Vue.directive('sticky', {
    inserted(el) {
        stickyfill.add(el);
    }
});

Vue.directive('focus', {
    bind(el, binding) {
        if (binding.value)
            el.focus();
    },
    inserted(el, binding) {
        if (binding.value)
            el.focus();
    },
    update(el, binding) {
        if (binding.value)
            el.focus();
    }
});

// Event constants
export const events = new Vue();

// Vue app

export const app = new Vue({
    el: '#app',
    store,
    router,
    components: {
        app: AppComponent,
    }
});

// connection detection
export function checkHttpConnection() {
    api.requestSingle('dummy', {});
}

echo.global.on('connect', () => store.commit('websocketConnection', true));
echo.global.on('reconnect', () => {
    store.commit('websocketConnection', true);
    checkHttpConnection();
});
echo.global.on('disconnect', () => {
    store.commit('websocketConnection', false);
    checkHttpConnection();
});
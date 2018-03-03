///<reference path="./types/index.d.ts" />

import './polyfill';
import 'babel-polyfill';

import Vue, {VNodeDirective} from 'vue';
import Vuelidate from 'vuelidate';
import IconComponent from 'vue-awesome/components/Icon.vue';
import * as ElementQueries from 'css-element-queries/src/ElementQueries';

import store from 'JS/store';
import echo from 'JS/echo';
import router from 'JS/router';
import AppComponent from './components/app.vue';
import LazyImgComponent from './components/widgets/image/lazy-img.vue';
import api from "JS/api";

// setup

Vue.use(Vuelidate);
ElementQueries.listen();

// Global Vue components and directives

Vue.component('icon', IconComponent);
Vue.component('lazy-img', LazyImgComponent);

Vue.directive('focus', {
    bind(el: HTMLElement, binding: VNodeDirective) {
        if (binding.value)
            el.focus();
    },
    inserted(el: HTMLElement, binding: VNodeDirective) {
        if (binding.value)
            el.focus();
    },
    update(el: HTMLElement, binding: VNodeDirective) {
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
export function checkHttpConnection(force = false) {
    if (force || !store.state.connection_http) {
        api.requestSingle('dummy', {});
    }
}

echo.global.on('connect', () => store.commit('websocketConnection', true));
echo.global.on('reconnect', () => {
    store.commit('websocketConnection', true);
    checkHttpConnection();
});
echo.global.on('disconnect', (reason: string) => {
    if (reason !== 'io client disconnect' && reason !== 'transport close') {
        store.commit('websocketConnection', false);
        checkHttpConnection(true);
    }
});
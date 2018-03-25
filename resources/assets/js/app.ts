///<reference path="./lib/types/index.d.ts" />

import './lib/polyfill';
import 'babel-polyfill';

import Vue, {VNodeDirective} from 'vue';
import Vuelidate from 'vuelidate';
import IconComponent from 'vue-awesome/components/Icon.vue';
import * as ElementQueries from 'css-element-queries/src/ElementQueries';

import store from 'JS/store';
import router from 'JS/router';
import {Store} from "vuex";

import EventsMixin, {EventsMixinInterface} from 'JS/components/mixins/events';
import AppComponent from './components/app.vue';
import LazyImgComponent from './components/widgets/image/lazy-img.vue';

declare global {
    interface Window {
        data: string | undefined
    }
}

// setup

Vue.use(Vuelidate);
ElementQueries.listen();

// Global Vue components, directives and mixins

Vue.component('icon', IconComponent);
Vue.component('lazy-img', LazyImgComponent);

Vue.mixin(EventsMixin);

declare module "vue/types/vue" {
    interface Vue extends EventsMixinInterface {}
}

Vue.directive('focus', {
    inserted(el: HTMLElement, binding: VNodeDirective) {
        if (binding.value) {
            el.focus();
        }
    }
});

// Vue app

export const app = new Vue({
    el: '#app',
    store: <Store<any>> store,
    router,
    components: {
        app: AppComponent,
    }
});
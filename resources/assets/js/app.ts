///<reference path="./lib/types/index.d.ts" />

import "../css/app.scss";

import './lib/polyfill';
import 'babel-polyfill';

import Vue, {VNode, VNodeDirective} from 'vue';
import Vuelidate from 'vuelidate';
import IconComponent from 'vue-awesome/components/Icon.vue';
import * as ElementQueries from 'css-element-queries/src/ElementQueries';
import seedrandom from 'seedrandom';

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

    // JSX
    namespace JSX {
        interface Element extends VNode {}
        interface ElementClass extends Vue {}
        interface IntrinsicElements {
            [elem: string]: any;
        }
    }
}

export const random = seedrandom(undefined, {entropy: true});

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
    render(h): VNode {
        return h('app');
    },
    router,
    components: {
        app: AppComponent,
    }
});
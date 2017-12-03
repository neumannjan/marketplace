import Vue from 'vue';
import Vuelidate from 'vuelidate';
import VueLazyLoad from 'vue-lazyload';
import {VueMasonryPlugin} from 'vue-masonry';

import store from './store/store';

import router from './routes/router';
import AppComponent from './components/app.vue';

Vue.use(Vuelidate);
Vue.use(VueMasonryPlugin);
Vue.use(VueLazyLoad, {
    observer: true,
    lazyComponent: true,
});

const app = new Vue({
    el: '#app',
    store,
    router,
    components: {
        app: AppComponent
    }
});
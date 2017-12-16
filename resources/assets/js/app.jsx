import Vue from 'vue';
import Vuelidate from 'vuelidate';
import VueLazyLoad from 'vue-lazyload';
import {VueMasonryPlugin} from 'vue-masonry';
import IconComponent from 'vue-awesome/components/Icon';

import store from './store/store';
import router from './routes/router';
import AppComponent from './components/app.vue';

// Vue plugins

Vue.use(Vuelidate);
Vue.use(VueMasonryPlugin);
Vue.use(VueLazyLoad, {
    observer: true,
    lazyComponent: true,
});

// Global Vue components

Vue.component('icon', IconComponent);

// Vue app

const app = new Vue({
    el: '#app',
    store,
    router,
    components: {
        app: AppComponent,
    }
});
import Vue from 'vue';
import Vuelidate from 'vuelidate';
import IconComponent from 'vue-awesome/components/Icon';

import store from './store/store';
import router from './routes/router';
import AppComponent from './components/app.vue';

// setup

Vue.use(Vuelidate);

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
import Vue from 'vue';
import Vuelidate from 'vuelidate';

import store from './store/store';
import router from './routes/router';

import AppComponent from './components/app.vue';

Vue.use(Vuelidate);

const app = new Vue({
    el: '#app',
    store,
    router,
    components: {
        app: AppComponent
    }
});
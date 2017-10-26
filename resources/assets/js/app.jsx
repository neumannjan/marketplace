import Vue from 'vue';

import store from './store/store';
import router from './routes/router';

import AppComponent from './components/app.vue';

const app = new Vue({
    el: '#app',
    store,
    router,
    components: {
        app: AppComponent
    }
});
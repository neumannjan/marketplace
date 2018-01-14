import Vue from 'vue';
import Vuelidate from 'vuelidate';
import IconComponent from 'vue-awesome/components/Icon';
import ElementQueries from 'css-element-queries/src/ElementQueries';

import store from 'JS/store';
import router from 'JS/router';
import AppComponent from './components/app.vue';

// setup

Vue.use(Vuelidate);
ElementQueries.listen();

// Global Vue components

Vue.component('icon', IconComponent);

// Event constants
const events = {
    VIEWPORT_CHANGE: 'viewport_change'
};

// Vue app

export const app = new Vue({
    el: '#app',
    store,
    router,
    components: {
        app: AppComponent,
    }
});

export default {
    app,
    events
}
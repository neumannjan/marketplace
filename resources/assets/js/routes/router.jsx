import Vue from 'vue';
import VueRouter from 'vue-router';

import IndexRoute from '../components/routes/index.vue';
import TestRoute from '../components/routes/test.vue';
import ErrorRoute from '../components/routes/404.vue';
import LoginRoute from '../components/routes/login.vue';
import GuestGuard from './guards/guest';

Vue.use(VueRouter);

export default new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'index',
            component: IndexRoute
        },
        {
            path: '/user/:name',
            name: 'user',
            component: TestRoute,
            props: true
        },
        {
            path: '/login',
            name: 'login',
            component: LoginRoute,
            ...GuestGuard
        },
        {
            path: '*',
            name: 'error',
            component: ErrorRoute
        },
    ]
});
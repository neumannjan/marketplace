import Vue from 'vue';
import VueRouter from 'vue-router';
import store from 'JS/store';

import IndexRoute from '../components/routes/index.vue';
import TestRoute from '../components/routes/test.vue';
import ErrorRoute from '../components/routes/404.vue';
import UserRoute from '../components/routes/user.vue';

import LoginRoute from '../components/routes/auth/login.vue';
import RegisterRoute from '../components/routes/auth/register.vue';
import PasswordEmailRoute from '../components/routes/auth/password-email.vue';
import PasswordResetRoute from '../components/routes/auth/password-reset.vue';

import UserNavigation from 'JS/components/routes/navigation/user-navigation';

import GuestGuard from './guards/guest';

Vue.use(VueRouter);

const cachedRoutes = [
    'index-route'
];

export const cached = (suffix = '') => suffix ? cachedRoutes.map((route) => `${route}-${suffix}`) : cachedRoutes;

export const events = new Vue();

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'index',
            component: IndexRoute
        },
        {
            path: '/test',
            name: 'test',
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
            path: '/register',
            name: 'register',
            component: RegisterRoute,
            ...GuestGuard
        },
        {
            path: '/password/reset',
            name: 'password-email',
            component: PasswordEmailRoute,
            ...GuestGuard
        },
        {
            path: '/password/reset/:token',
            name: 'password-reset',
            component: PasswordResetRoute,
            props: true,
            ...GuestGuard
        },
        {
            path: '/user/:username',
            name: 'user',
            components: {
                default: UserRoute,
                navigation: UserNavigation,
            },
            props: {
                default: true
            },
        },
        {
            path: '/404',
            name: 'error',
            component: ErrorRoute
        },
        {
            path: '*',
            component: ErrorRoute
        },
    ]
});

router.afterEach(to => {
    store.commit('routeHasNavigation', !!to.matched[0].components.navigation);
});

export default router;
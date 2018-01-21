import Vue from 'vue';
import VueRouter from 'vue-router';

import IndexRoute from '../components/routes/index';
import TestRoute from '../components/routes/test';
import ErrorRoute from '../components/routes/404';
import UserRoute from '../components/routes/user';
import OfferRoute from '../components/routes/offer';

import LoginRoute from '../components/routes/auth/login';
import RegisterRoute from '../components/routes/auth/register';
import PasswordEmailRoute from '../components/routes/auth/password-email';
import PasswordResetRoute from '../components/routes/auth/password-reset';

import UserNavigation from 'JS/components/routes/navigation/user-navigation';

import GuestGuard from './guards/guest';
import AuthGuard from './guards/auth';

import store from 'JS/store';

Vue.use(VueRouter);

const cachedRouteComponents = [
    'index-route'
];

export const cached = (suffix = '') => suffix ? cachedRouteComponents.map((route) => `${route}-${suffix}`) : cachedRouteComponents;

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
            path: '/me',
            name: 'me',
            redirect: to => {
                if (store.state.is_authenticated && store.state.user)
                    return {name: 'user', params: {username: store.state.user.username}};
                else
                    return {name: 'login'}
            },
            ...AuthGuard
        },
        {
            path: '/offer/:id',
            name: 'offer',
            components: {
                default: OfferRoute,
                //navigation: UserNavigation,
            },
            props: {
                default: route => ({id: parseInt(route.params.id)})
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

router.getCurrentRouteMainComponent = () => {
    const matched = router.currentRoute.matched;
    return matched[matched.length - 1].instances.default;
};

export default router;
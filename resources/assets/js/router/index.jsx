import Vue from 'vue';
import VueRouter from 'vue-router';

import IndexRoute from '../components/routes/index';
import TestRoute from '../components/routes/test';
import ErrorRoute from '../components/routes/404';
import UserRoute from '../components/routes/user';
import OfferRoute from '../components/routes/offer';
import SearchRoute from '../components/routes/search';

import LoginRoute from '../components/routes/auth/login';
import RegisterRoute from '../components/routes/auth/register';
import PasswordEmailRoute from '../components/routes/auth/password-email';
import PasswordResetRoute from '../components/routes/auth/password-reset';

import UserNavigation from 'JS/components/routes/navigation/user-navigation';

import GuestGuard from './guards/guest';
import AuthGuard from './guards/auth';

import OfferModal from '../components/routes/modal/offer-modal';

import store from 'JS/store';

Vue.use(VueRouter);

const cachedRouteComponents = [
    'index-route'
];

export const cached = (suffix = '') => suffix ? cachedRouteComponents.map((route) => `${route}-${suffix}`) : cachedRouteComponents;

export const events = new Vue();

export const queryRouter = {
    offer: {
        component: OfferModal,
        size: 'xl'
    }
};

const router = new VueRouter({
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {

        const promise = to.meta.async ?
            new Promise(resolve => events.$once('loaded', resolve))
            : Promise.resolve();

        if (savedPosition) {
            return promise.then(() => savedPosition);
        }

        return undefined;
    },
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
            meta: {
                async: true
            }
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
            component: OfferRoute,
            props: route => ({id: parseInt(route.params.id)}),
        },
        {
            path: '/search',
            name: 'search',
            component: SearchRoute
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

router.afterEach(() => {
    store.commit('addReRoute');
});

router.getRouteMainComponent = (route = router.currentRoute) => {
    const matched = route.matched;
    return matched[matched.length - 1].instances.default;
};

router.routesMatch = (route1, route2 = router.currentRoute) => {
    if (route1.path === route2.path)
        return true;

    if (route1.name !== route2.name)
        return false;

    let match = true;
    for (let [key, param] of Object.entries(route2.params)) {
        match = match && route1.params[key] === param;
        if (!match) break;
    }

    return match;
};

export default router;
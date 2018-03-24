import Vue, {VueConstructor} from 'vue';
import VueRouter, { Route, Location } from 'vue-router';

import IndexRoute from '../components/routes/index.vue';
import TestRoute from '../components/routes/test.vue';
import ErrorRoute from '../components/routes/404.vue';
import UserRoute from '../components/routes/user.vue';
import OfferRoute from '../components/routes/offer.vue';
import SearchRoute from '../components/routes/search.vue';

import OfferFormRoute from '../components/routes/offer-form.vue';

import LoginRoute from '../components/routes/auth/login.vue';
import RegisterRoute from '../components/routes/auth/register.vue';
import PasswordEmailRoute from '../components/routes/auth/password-email.vue';
import PasswordResetRoute from '../components/routes/auth/password-reset.vue';

import UserNavigation from 'JS/components/routes/navigation/user-navigation.vue';

import GuestGuard from './guards/guest';
import AuthGuard from './guards/auth';

import OfferModal from '../components/routes/modal/offer-modal.vue';

import store from 'JS/store';
import EventListener from "JS/lib/event-listener";
import { User } from 'JS/api/types';
import events from 'JS/events';

Vue.use(VueRouter);

export type CachedRouteComponents = string[];

/*
 * List of route components that should be cached
 */
const cachedRouteComponents = [
    'search-route'
] as CachedRouteComponents;

/**
 * Get list of route components that should be cached
 * @param {string} suffix String suffix to append to each component
 * @returns {CachedRouteComponents}
 */
export function cached(suffix = ''): CachedRouteComponents {
    if(suffix) {
        return cachedRouteComponents.map((route) => `${route}-${suffix}`);
    } else {
        return cachedRouteComponents;
    }
}

/**
 * Route events enum
 */
export enum RouteEvents {
    Loading = 'Loading',
    Loaded = 'Loaded',
    UserNavigation = 'UserNavigation',
}

interface Payloads {
    [RouteEvents.Loading]: undefined,
    [RouteEvents.Loaded]: undefined,
    [RouteEvents.UserNavigation]: User | null
}

/**
 * Route events
 */
export const routeEvents = new EventListener<Payloads, RouteEvents>();

/**
 * Interface for a router that displays modal windows based on route query parameters
 */
export interface QueryModalRouter {
    [queryParam: string]: {
        component: Vue | VueConstructor,

        /**
         * Modal window size
         */
        size: string
    }
}

export const queryModalRouter: QueryModalRouter = {
    offer: {
        component: OfferModal,
        size: 'xl'
    }
};

/**
 * Get the main Vue component of route
 * @param {Route} route
 */
export function getRouteMainComponent(route: Route = router.currentRoute): null | (Vue & {isTopLevelRoute?: boolean}) {
    const matched = route.matched;
    if (matched.length > 0) {
        return matched[matched.length - 1].instances.default;
    } else {
        return null;
    }
}

/**
 * Check whether two routes are identical
 * @param {Route | Location} route1
 * @param {Route | Location} route2
 * @param {boolean} ignoreParams
 * @return {boolean}
 */
export function routesMatch(route1: Route | Location, route2: Route | Location = router.currentRoute, ignoreParams: boolean = false): boolean {
    if (!route1 || !route2)
        return false;

    if (route1.path === route2.path)
        return true;

    if (route1.name !== route2.name)
        return false;

    if (ignoreParams)
        return true;

    let match = true;
    for (let [key, param] of Object.entries(route2.params ? route2.params : {})) {
        match = match && route1.params !== undefined && route1.params[key] === param;
        if (!match) break;
    }

    return match;
}

const router = new VueRouter({
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        const promise = to.meta.async ?
            new Promise<void>(resolve => routeEvents.once(RouteEvents.Loaded, resolve))
            : Promise.resolve();

        if (savedPosition) {
            return promise.then(() => savedPosition);
        }

        return undefined;
    },
    routes: [
        // Index page
        {
            path: '/',
            name: 'index',
            component: IndexRoute,
            meta: {
                refreshOnReconnect: true
            }
        },

        // Test page TODO remove
        {
            path: '/test',
            name: 'test',
            component: TestRoute,
            props: true
        },

        // Auth pages
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

        // Display pages
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
                async: true,
                refreshOnReconnect: true
            }
        },
        {
            path: '/me',
            name: 'me',
            redirect: to => {
                if (!!store.state.user)
                    return {name: 'user', params: {username: store.state.user.username}};
                else
                    return {name: 'login'}
            },
            ...AuthGuard
        },
        {
            path: '/offer/:id(\\d+)',
            name: 'offer',
            component: OfferRoute,
            props: route => ({id: parseInt(route.params.id)}),
            meta: {
                refreshOnReconnect: true
            }
        },
        {
            path: '/search/:query?',
            name: 'search',
            component: SearchRoute,
            props: true,
            meta: {
                refreshOnReconnect: true
            }
        },

        // Modify pages
        {
            path: '/offer/create',
            name: 'offer-create',
            component: OfferFormRoute,
            ...AuthGuard
        },

        // error
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

export default router;
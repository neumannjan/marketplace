import Vue, {VueConstructor} from 'vue';
import VueRouter, { Route, Location } from 'vue-router';

import IndexRoute from './components/routes/index.vue';
import TestRoute from './components/routes/test.vue';
import ErrorRoute from './components/routes/404.vue';
import UserRoute from './components/routes/user.vue';
import OfferRoute from './components/routes/offer.vue';
import SearchRoute from './components/routes/search.vue';

import OfferFormRoute from './components/routes/offer-form.vue';

import LoginRoute from './components/routes/auth/login.vue';
import RegisterRoute from './components/routes/auth/register.vue';
import PasswordEmailRoute from './components/routes/auth/password-email.vue';
import PasswordResetRoute from './components/routes/auth/password-reset.vue';

import AdminRoute from './components/routes/admin/index.vue';
import AdminReportedRoute from './components/routes/admin/reported.vue';
import AdminBannedRoute from './components/routes/admin/banned.vue';

import UserNavigation from 'JS/components/routes/navigation/user-navigation.vue';
import AdminNavigation from 'JS/components/routes/navigation/admin-navigation';

import OfferModal from './components/routes/modal/offer-modal.vue';

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

interface ChildRoute extends Route {
    meta: {
        [index: string]: any,
        parent: string
    }
}

/**
 * Check whether two routes are identical
 * @param {Route | Location} route1
 * @param {Route | Location} route2
 * @param {boolean} ignoreParams
 * @param {boolean} checkParent
 * @return {boolean}
 */
export function routesMatch(route1: Route | Location,
    route2: Route | Location = router.currentRoute,
    ignoreParams: boolean = false,
    checkParent: boolean = false): boolean {
    if (!route1 || !route2)
        return false;

    if (route1.path === route2.path)
        return true;

    function isChildRoute(route: Route | Location): route is ChildRoute {
        return !!(<Route>route).fullPath && !!(<ChildRoute>route).meta.parent;
    }

    if (route1.name !== route2.name
        && !(checkParent && isChildRoute(route1) && isChildRoute(route2) && route1.meta.parent === route2.meta.parent)
        && !(checkParent && isChildRoute(route1) && route1.meta.parent === route2.name)
        && !(checkParent && isChildRoute(route2) && route2.meta.parent === route1.name))
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

const router: VueRouter = new VueRouter({
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
        },
        {
            path: '/register',
            name: 'register',
            component: RegisterRoute,
        },
        {
            path: '/password/reset',
            name: 'password-email',
            component: PasswordEmailRoute,
        },
        {
            path: '/password/reset/:token',
            name: 'password-reset',
            component: PasswordResetRoute,
            props: true,
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
        },
        {
            path: '/offer/edit/:id(\\d+)',
            name: 'offer-edit',
            component: OfferFormRoute,
            props: route => ({id: parseInt(route.params.id)}),
        },

        //administration
        {
            path: '/admin',
            components: {
                default: AdminRoute,
                navigation: AdminNavigation,
            },
            children: [
                {
                    path: '/',
                    name: 'admin',
                    redirect: () => ({name: 'admin-reported'})
                },
                {
                    path: 'reported',
                    name: 'admin-reported',
                    component: AdminReportedRoute,
                    meta: {
                        parent: 'admin'
                    }
                },
                {
                    path: 'banned',
                    name: 'admin-banned',
                    component: AdminBannedRoute,
                    meta: {
                        parent: 'admin'
                    }
                },
            ]
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
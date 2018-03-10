import Vuex, {ActionContext, ActionTree, GetterTree, MutationTree, Store} from "vuex";
import api from "JS/api";
import Vue from "vue";
import persistedState from 'vuex-persistedstate';
import {
    CachedResponse,
    FlashMessageWithKey,
    GlobalResponse,
    InitialResponse,
    OfferRequestScope,
    RequestScope
} from "JS/api/types";
import {StrictStore} from "JS/lib/strict-store";

Vue.use(Vuex);

/**
 * Replaces values of `obj` with those from `newObj`. Nested arrays and objects are merged.
 * Keys of `newObj` that are not already in `obj` are ignored.
 * @param {T} obj
 * @param {T} newObj
 * @param {boolean} createNew
 * @return {T}
 */
function updateObject<T extends {[key: string]: any} = {[key: string]: any}>
(obj: T, newObj: T, createNew: boolean = false): T {
    let to = createNew ? <T>{} : obj;
    for (let [key, value] of Object.entries(newObj)) {
        if (obj[key] !== undefined) {
            if (obj[key] instanceof Array && value instanceof Array) {
                to[key] = [...obj[key], ...value];
            } else if (obj[key] instanceof Object && value instanceof Object) {
                to[key] = {...obj[key], ...value};
            } else {
                to[key] = value;
            }
        }
    }

    return to;
}

/**
 * Notification interface
 */
export interface Notification {
    id: string,
    message: string,
    type: string
}

/**
 * A map of appropriate API scopes per request type
 */
export interface AppropriateScopes {
    user: RequestScope,
    publicOffer: RequestScope,
    offer: OfferRequestScope
}

/**
 * State declaration
 */
export interface State extends InitialResponse {
    connection_http: null | boolean,
    connection_websocket: null | boolean,
    notifications: {[key: string]: Notification},
    reRoutedTimes: number,
    cached: CachedResponse,
    _cached_loaded: boolean,
}

/**
 * Initial store state
 */
const state: State = {
    token: null,
    locale: 'en',
    is_authenticated: false,
    user: null,
    is_admin: false,
    connection_http: null,
    connection_websocket: null,
    flash: {},
    notifications: {},
    messages: {},
    reRoutedTimes: -1,
    cached: {
        currencies: {}
    },
    _cached_loaded: false,
    socket_host: null
};

/**
 * Store mutations definition
 */
const mutations = {
    global(state: State, data: GlobalResponse | InitialResponse) {
        updateObject<GlobalResponse | InitialResponse>(state, data);
    },
    logout(state: State) {
        state.is_authenticated = false;
    },
    token(state: State, token: string) {
        state.token = token;
    },
    removeFlash(state: State, key: string) {
        Vue.delete(state.flash, key);
    },
    addFlash(state: State, flash: FlashMessageWithKey) {
        Vue.set(state.flash, flash.key, flash);
    },
    addNotification(state: State, notification: Notification) {
        if (!notification.id) {
            notification.id = (Math.random() + 1).toString(36).substr(2, 5);
        }

        const notifications = state.notifications;
        delete notifications[notification.id];

        state.notifications = {[notification.id]: notification, ...notifications};
    },
    removeNotification(state: State, id: string) {
        Vue.delete(state.notifications, id);
    },
    httpConnection(state: State, has: boolean) {
        state.connection_http = has;
    },
    websocketConnection(state: State, has: boolean) {
        state.connection_websocket = has;
    },
    addReRoute(state: State) {
        ++state.reRoutedTimes;
    },
    cached(state: State, data: CachedResponse) {
        state._cached_loaded = true;
        state.cached = updateObject<CachedResponse>(state.cached, data, true);
    }
};

/**
 * Store actions definition
 */
const actions = {
    logout(context: ActionContext<State, State>) {
        return api.requestSingle('logout')
            .then(() => store.commit('logout'));
    },
    requestCached(context: ActionContext<State, State>) {
        if (context.state._cached_loaded) return;

        return api.requestSingle<CachedResponse>('cached')
            .then((data: CachedResponse) => store.commit('cached', data));
    }
};

/**
 * Store getters definition
 */
const getters = {
    scope(state: State): AppropriateScopes {
        function considerAdmin<Scope extends string>(scope: Scope): Scope {
            return state.is_admin ? <Scope> 'unlimited' : scope;
        }

        return {
            user: considerAdmin<RequestScope>('public'),
            publicOffer: considerAdmin<RequestScope>('public'),
            offer: considerAdmin<OfferRequestScope>(state.is_authenticated ? 'auth' : 'public'),
        };
    }
};

const store: StrictStore<State, typeof mutations, typeof actions, typeof getters> = new Vuex.Store({
    plugins: [persistedState<State>({paths: ['cached', '_cached_loaded']})],
    strict: true,
    state,
    mutations,
    actions,
    getters
});

export const initialData = window.data ? (<InitialResponse> JSON.parse(atob(window.data))) : null;

if (initialData) {
    store.commit('global', initialData);
}

export const helpers = {
    /**
     * Get translation for key
     * @param {string} key
     * @param {string[]} parameters
     * @return {string}
     */
    trans(key: string, parameters?: string[]) {
        if (!store.state.messages)
            return key;

        //retrieve the value based on dot notation of key
        let value: undefined | string | object = key.split('.').reduce(function (a: any, b: string) {
            if (a && (typeof a === 'object') && a[b])
                return a[b];
            else
                return null;
        }, store.state.messages);

        if (!value || typeof value === "object")
            return key;

        if (!parameters || parameters.length === 0)
            return value;

        //replace parameters with values
        for (let [param, replacement] of Object.entries(parameters)) {
            value = value.replace(':' + param, replacement);
        }

        return value;
    }
};

export async function cached() {
    await store.dispatch('requestCached');
    return store.state.cached;
}

export default store;
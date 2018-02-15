import Vue from 'vue';
import Vuex from 'vuex';
import api from 'JS/api';
import persistedState from 'vuex-persistedstate';

Vue.use(Vuex);

function setGlobal(of, data, createNew = false) {
    let to = createNew ? {} : of;
    for (let [key, value] of Object.entries(data)) {
        if (of[key] !== undefined) {
            if (of[key] instanceof Array && value instanceof Array) {
                to[key] = [...of[key], ...value];
            } else if (of[key] instanceof Object && value instanceof Object) {
                to[key] = {...of[key], ...value};
            } else {
                to[key] = value;
            }
        }
    }

    return to;
}

const store = new Vuex.Store({
    plugins: [persistedState({paths: ['cached', '_cached_loaded', 'persisted']})],
    strict: true,
    state: {
        token: false,
        locale: 'en',
        is_authenticated: false,
        user: null,
        is_admin: false,
        connection_http: null,
        connection_websocket: null,
        flash: {},
        notifications: {},
        messages: [],
        currencies: [],
        reRoutedTimes: -1,
        cached: {
            currencies: []
        },
        _cached_loaded: false,
        persisted: {},
        socket_host: null
    },
    mutations: {
        global(state, data) {
            setGlobal(state, data);
        },
        logout(state) {
            state.is_authenticated = false;
        },
        token(state, token) {
            state.token = token;
        },
        removeFlash(state, key) {
            Vue.delete(state.flash, key);
        },
        addFlash(state, flash) {
            Vue.set(state.flash, flash.key, flash);
        },
        addNotification(state, notification) {
            if (!notification.id && notification.id !== 0) {
                notification.id = (Math.random() + 1).toString(36).substr(2, 5);
            }

            const notifications = state.notifications;
            delete notifications[notification.id];

            state.notifications = {[notification.id]: notification, ...notifications};
        },
        removeNotification(state, id) {
            const notifications = state.notifications;
            delete notifications[id];
            state.notifications = notifications;
        },
        httpConnection(state, value) {
            state.connection_http = value;
        },
        websocketConnection(state, value) {
            state.connection_websocket = value;
        },
        addReRoute(state) {
            ++state.reRoutedTimes;
        },
        cached(state, data) {
            state._cached_loaded = true;
            state.cached = setGlobal(state.cached, data, true);
        },
        persist(state, object) {
            if (typeof object !== "object")
                throw new Error('persist() has to be called with an object');

            state.persisted = {...state.persisted, ...object};
        }
    },
    actions: {
        logout(context) {
            return api.requestSingle('logout')
                .then(() => context.commit('logout'));
        },
        requestCached(context) {
            if (context.state._cached_loaded) return;

            return api.requestSingle('cached')
                .then(data => context.commit('cached', data));
        }
    },
    getters: {
        scope: state => {
            const considerAdmin = (scope) => state.is_admin ? 'unlimited' : scope;

            return {
                user: considerAdmin('public'),
                publicOffer: considerAdmin('public'),
                offer: considerAdmin(state.is_authenticated ? 'auth' : 'public'),
            };
        }
    }
});

if (data) {
    store.commit('global', JSON.parse(atob(data)));
}

export const helpers = {
    trans(key, parameters) {
        if (!store.state.messages)
            return key;

        let value = key.split('.').reduce(function (a, b) {
            if (a && (typeof a === 'object') && a[b])
                return a[b];
            else
                return null;
        }, store.state.messages);

        if (!value)
            return key;

        if (!parameters || parameters.length === 0)
            return value;

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
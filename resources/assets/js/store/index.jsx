import Vue from 'vue';
import Vuex from 'vuex';
import api from 'JS/api';

import cache from './modules/cache';

Vue.use(Vuex);

const store = new Vuex.Store({
    modules: {
        cache
    },
    strict: true,
    state: {
        is_authenticated: false,
        user: null,
        is_admin: false,
        connection_lost: false,
        token: false,
        flash: {},
        messages: [],
    },
    mutations: {
        global(state, data) {
            for (let [key, value] of Object.entries(data)) {
                if (state[key] !== undefined) {

                    if (state[key] instanceof Array && value instanceof Array) {
                        state[key] = [].concat(state[key], value);
                    } else if (state[key] instanceof Object && value instanceof Object) {
                        state[key] = Object.assign({}, state[key], value);
                    } else {
                        state[key] = value;
                    }
                }
            }
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
        connection(state, value) {
            state.connection_lost = !value;
        }
    },
    actions: {
        logout(context) {
            api.requestSingle('logout')
                .then(() => {
                    context.commit('logout');
                });
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

export default store;
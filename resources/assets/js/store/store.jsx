import Vue from 'vue';
import Vuex from 'vuex';
import merge from 'deepmerge';
import api from '../api';

Vue.use(Vuex);

let store = new Vuex.Store({
    state: {
        is_authenticated: false,
        token: false
    },
    modules: {},
    mutations: {
        merge(state, data) {
            for (let [key, value] of Object.entries(data)) {
                if (Array.isArray(value) && Array.isArray(state[key])) {
                    state[key] = merge(state[key], value);
                } else {
                    state[key] = value;
                }
            }
        },
        auth(state, authState) {
            state.is_authenticated = authState;
        },
        logout(state) {
            state.is_authenticated = false;
        },
        token(state, token) {
            state.token = token;
        }
    },
    actions: {
        logout(context) {
            api.SingleRequest('logout')
                .success((result) => {
                    context.commit('logout');
                    context.commit('token', result.token);
                })
                .fire();
        }
    }
});

if (data) {
    store.commit('merge', JSON.parse(atob(data)));
}

export default store;
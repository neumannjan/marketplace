import Vuex, {ActionContext} from "vuex";
import api from "JS/api";
import Vue from "vue";
import {FlashMessageWithKey, GlobalResponse, InitialResponse, OfferRequestScope, RequestScope} from "JS/api/types";
import {StrictStore} from "JS/lib/strict-store";
import {Notification} from "JS/lib/notifications/typings";
import initial from './initial';
import Lang, {TranslationReplacements} from 'lang.js';

Vue.use(Vuex);

/**
 * Replaces values of `obj` with those from `newObj`. Nested arrays and objects are merged.
 * Keys of `newObj` that are not already in `obj` are ignored.
 * @param {T} obj
 * @param {T} newObj
 * @param {boolean} createNew
 * @return {T}
 */
function updateObject<T extends { [key: string]: any } = { [key: string]: any }>
(obj: T, newObj: T, createNew: boolean = false): T {
    let to = createNew ? <T>{} : obj;
    for (let [key, value] of Object.entries(newObj)) {
        if (obj[key] !== undefined) {
            if (obj[key] instanceof Array && value instanceof Array) {
                const newArray = (obj[key] as Array<any>).filter((val: any) => {
                    return (value as Array<any>).indexOf(val) === -1;
                });

                newArray.push(...value);
                to[key] = newArray;
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
 * Message translation tool
 */
let lang = new Lang();

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
    notifications: { [key: string]: Notification },
    reRoutedTimes: number,
}

/**
 * Initial store state
 */
const state: State = {
    name: 'Marketplace',
    token: null,
    locale: 'en',
    fallback_locale: 'en',
    available_locales: ['en'],
    currency_default: 0,
    locale_names: {},
    user: null,
    is_admin: false,
    connection_http: null,
    connection_websocket: null,
    max_file_uploads: 0,
    max_file_kb: 0,
    flash: {},
    notifications: {},
    messages: {},
    reRoutedTimes: -1,
    currencies: {},
    socket_host: null
};

/**
 * Store mutations definition
 */
const mutations = {
    global(state: State, data: GlobalResponse | InitialResponse) {
        updateObject<GlobalResponse | InitialResponse>(state, data);

        lang.setLocale(state.locale);

        if ((<InitialResponse>data).messages)
            lang.setMessages(state.messages);
    },
    toggleLocale(state: State) {
        let index = state.available_locales.indexOf(state.locale) + 1;

        if (index >= state.available_locales.length) {
            index = 0;
        }

        state.locale = state.available_locales[index];
        lang.setLocale(state.locale);
    },
    logout(state: State) {
        state.user = null;
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
};

/**
 * Store actions definition
 */
const actions = {
    logout(context: ActionContext<State, State>) {
        return api.requestSingle('logout')
            .then(() => store.commit('logout'));
    },
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
            offer: considerAdmin<OfferRequestScope>(!!state.user ? 'auth' : 'public'),
        };
    },
    trans(state: State) {
        return (key: string, replacements?: TranslationReplacements): string => {
            return lang.get(key, replacements, state.locale);
        }
    },
    transChoice(state: State) {
        return (key: string, number: number, replacements?: TranslationReplacements): string => {
            return lang.choice(key, number, replacements, state.locale);
        }
    },
    localeName(state: State) {
        return (locale: string) => {
            return state.locale_names[locale] ? state.locale_names[locale] : locale;
        }
    }
};

const store: StrictStore<State, typeof mutations, typeof actions, typeof getters> = new Vuex.Store({
    strict: true,
    state,
    mutations,
    actions,
    getters
});

store.commit('global', initial.state);

export default store;
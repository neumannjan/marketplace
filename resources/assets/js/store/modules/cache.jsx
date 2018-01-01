const EXPIRATION_TIME_MS = 5 * 60 * 1000; //TODO allow per entry ?

const put = (state, namespace, entries) => {
    const toAssign = {};

    for (let [key, entry] of Object.entries(entries)) {
        toAssign[key] = {
            timestamp: Date.now(),
            value: entry
        }
    }

    if (state[namespace]) {
        state[namespace] = {...state[namespace], ...toAssign};
    } else {
        state[namespace] = toAssign;
    }
};

export default {
    namespaced: true,
    state: {},
    mutations: {
        put(state, data) {
            put(state, data.namespace, data.entries);
        },
        putUsers(state, data) {
            let users = {};


            for (let user of data) {
                users[user.username] = user;
            }

            put(state, 'users', users);
        },
        putOffers(state, data) {
            let offers = {};
            let users = {};


            for (let offer of data) {
                offers[offer.id] = offer;
                users[offer.author.username] = offer.author;
            }

            put(state, 'offers', offers);
            put(state, 'users', users);
        }
    },
    getters: {
        get: (state) => (namespace, key) => {
            if (!state[namespace])
                return undefined;

            const entry = state[namespace][key];

            if (entry === undefined)
                return undefined;

            const age = ((Date.now()) - entry.timestamp);
            console.log(age, EXPIRATION_TIME_MS, entry.value);

            if (age > EXPIRATION_TIME_MS)
                return undefined;

            return entry.value;
        }
    }
};
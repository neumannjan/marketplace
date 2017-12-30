import axios from 'axios';
import store from './store/store';

const config = () => ({
    headers: {
        'X-CSRF-TOKEN': store.state.token,
        'X-Requested-With': 'XMLHttpRequest'
    }
});

const defaultReject = (reject, isHttp) => {
    return (error) => {
        if (isHttp) {
            if (error.response === undefined) {
                store.commit('connection', false);
            }
        }

        let result = {
            http: null,
            api: null
        };

        result[isHttp ? 'http' : 'api'] = error;
        reject(result);
    };
};

const reportConnection = () => {
    store.commit('connection', true);
};

/**
 * @param allParams Parameters of all requests
 * @param includeGlobal Whether the 'global' request should be included
 * @returns {Promise<Object>}
 */
const requestMultiple = (allParams, includeGlobal = true) => {
    return new Promise((resolve, reject) => {
        if (includeGlobal && !allParams.global)
            allParams.global = {};

        const data = {
            api: JSON.stringify(allParams)
        };

        const then = response => {
            reportConnection();

            if (includeGlobal) {
                const global = response.data.global.result;
                store.commit('global', global);
            }

            resolve(response.data);
        };

        axios.post('/api', data, config())
            .then(then)
            .catch(defaultReject(reject, true));
    });
};

/**
 * @param name Request name
 * @param params Request parameters
 * @param includeGlobal Whether the 'global' request should be included
 * @returns {Promise<Object>}
 */
const requestSingle = (name, params = {}, includeGlobal = true) => {
    return new Promise((resolve, reject) => {

        const then = response => {
            reportConnection();

            let data = response[name];

            if (data.success)
                resolve(data.result);
            else
                defaultReject(reject, false)(data.result);
        };

        const data = {
            [name]: params
        };

        requestMultiple(data, includeGlobal)
            .then(then)
            .catch(reject);
    });
};

/**
 * @param url
 * @returns {Promise<Object>}
 */
const requestByURL = (url) => {
    return new Promise((resolve, reject) => {

        const then = response => {
            reportConnection();

            let name = null;
            for (let [key, data] of Object.entries(response.data)) {
                if (key === 'global') {
                    store.commit('global', data.result);
                } else {
                    name = key;
                }
            }

            const data = response.data[name];
            if (data.success)
                resolve(data.result);
            else
                defaultReject(reject, false)(data.result);
        };

        axios.post(url, {}, config())
            .then(then)
            .catch(defaultReject(reject, true));
    });
};

export default {
    requestMultiple,
    requestSingle,
    requestByURL
};
import axios from 'axios';
import store from './store/store';

let config = () => ({
    headers: {
        'X-CSRF-TOKEN': store.state.token,
        'X-Requested-With': 'XMLHttpRequest'
    }
});

let defaultReject = (reject, isHttp) => {
    return (error) => {
        if (process.env.NODE_ENV === 'development') {

            if (isHttp) {

                if (error.response.status) {
                    alert(JSON.stringify(error.message, null, 2));
                }
            }
        }

        let result = {
            http: null,
            api: null
        };

        result[isHttp ? 'http' : 'api'] = error;
        console.log(result);
        reject(result);
    };
};

/**
 * @param allParams Parameters of all requests
 * @param includeGlobal Whether the 'global' request should be included
 * @returns {Promise<Object>}
 */
let requestMultiple = (allParams, includeGlobal = true) => {
    return new Promise((resolve, reject) => {
        if (includeGlobal && !allParams.global)
            allParams.global = {};

        let data = {
            api: JSON.stringify(allParams)
        };

        let then = response => {
            if (includeGlobal) {
                let global = response.data.global.result;
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
let requestSingle = (name, params = {}, includeGlobal = true) => {
    return new Promise((resolve, reject) => {

        let then = response => {
            let data = response[name];

            if (data.success)
                resolve(data.result);
            else
                defaultReject(reject, false)(data.result);
        };

        let data = {
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
let requestByURL = (url) => {
    return new Promise((resolve, reject) => {

        let then = response => {
            let name = null;
            for (let [key, data] of Object.entries(response.data)) {
                if (key === 'global') {
                    store.commit('global', data.result);
                } else {
                    name = key;
                }
            }

            let data = response.data[name];
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
import axios, {AxiosError, AxiosResponse} from 'axios';
import store from 'JS/store';

const config = () => ({
    headers: {
        'X-CSRF-TOKEN': store.state.token,
        'X-Requested-With': 'XMLHttpRequest'
    }
});

/**
 * API error response
 */
export type ApiError = { http: AxiosError } | { api: Object | undefined };

/**
 * Return a reject function for an HTTP error.
 * @param {(error: ApiError) => void} reject
 * @return {(error: AxiosError) => void}
 */
function promiseHttpErrorReject(reject: (error: ApiError) => void): ((error: AxiosError) => void) {
    return (error: AxiosError) => {
        if (error.response === undefined) {
            // Tell Vue that we do not have access to the internet.
            store.commit('httpConnection', false);
        }

        reject({http: error});
    }
}

/**
 * Return a reject function for an API error.
 * @param {(error: ApiError) => void} reject
 * @return {(error: Object) => void}
 */
function promiseApiErrorReject(reject: (error: ApiError) => void): ((error: Object | undefined) => void) {
    return (error: Object | undefined) => {
        reject({api: error});
    }
}

/**
 * Tell Vue that we have access to the internet.
 */
const reportConnection = () => {
    store.commit('httpConnection', true);
};

/**
 * Data of a single API request.
 */
export interface SingleApiRequestData {
    [name: string]: any
}

/**
 * Data of a single API response.
 */
export interface SingleApiResponseData {
    [name: string]: any
}

/**
 * Raw single API response.
 */
interface RawSingleApiResponse {
    success: boolean
    result: SingleApiResponseData
}

/**
 * Data of a composite API request.
 */
export interface CompositeApiRequestData {
    global?: SingleApiRequestData,

    [name: string]: SingleApiRequestData | undefined
}

/**
 * Data of a composite API response.
 */
export interface CompositeApiResponseData {
    global?: RawSingleApiResponse,

    [name: string]: RawSingleApiResponse | undefined
}

/**
 * Raw composite API response.
 */
interface RawCompositeApiResponse extends CompositeApiResponseData {
}

/**
 * Response returned by Axios for an API request.
 */
interface AxiosApiResponse extends AxiosResponse {
    data: RawCompositeApiResponse
}

/**
 * Perform a composite API call.
 * @param {CompositeApiRequestData} allParams Parameters of all requests
 * @param {boolean} includeGlobal Whether the 'global' request should be included
 * @return {Promise<CompositeApiResponseData>}
 */
const requestMultiple = (allParams: CompositeApiRequestData, includeGlobal: boolean = true): Promise<CompositeApiResponseData> => {
    return new Promise((resolve: (data: CompositeApiResponseData) => any, reject: (error: ApiError) => void) => {
        if (includeGlobal && !allParams.global)
            allParams.global = {};

        const data = {
            api: JSON.stringify(allParams)
        };

        const then = (response: AxiosApiResponse) => {
            reportConnection();

            if (includeGlobal && response.data.global && response.data.global.success) {
                const global = response.data.global.result;
                store.commit('global', global);
            }

            resolve(response.data);
        };

        axios.post('/api', data, config())
            .then(then)
            .catch(promiseHttpErrorReject(reject));
    });
};

/**
 * Perform an API call.
 * @param {string} name Request name
 * @param {SingleApiRequestData} params Request parameters
 * @param {boolean} includeGlobal Whether the 'global' request should be included
 * @returns {Promise<SingleApiResponseData>}
 */
const requestSingle = (name: string, params: SingleApiRequestData = {}, includeGlobal: boolean = true) => {
    if (params instanceof FormData) {
        return requestByURL(`/api/${name}`, params);
    }

    return new Promise((resolve: (data: SingleApiResponseData) => any, reject: (error: ApiError) => any) => {

        const then = (response: CompositeApiResponseData) => {
            reportConnection();

            let data = response[name];

            if (data) {
                if (data.success)
                    resolve(data.result);
                else
                    promiseApiErrorReject(reject)(data.result);
            } else {
                throw response;
            }
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
 * @param {string} url API endpoint URL
 * @param {SingleApiRequestData} data
 * @returns {Promise<Object>}
 */
const requestByURL = (url: string, data: SingleApiRequestData = {}) => {
    return new Promise((resolve, reject) => {

        const then = (response: AxiosApiResponse) => {
            reportConnection();

            let name: string | null = null;
            for (let [key, data] of Object.entries(response.data)) {
                if (data) {
                    if (key === 'global') {
                        store.commit('global', data.result);
                    } else {
                        name = key;
                    }
                }
            }

            if (name && response.data[name]) {
                const data = response.data[name];
                if (data && data.success)
                    resolve(data.result);
                else
                    promiseApiErrorReject(reject)(data ? data.result : undefined);
            } else {
                promiseApiErrorReject(reject)(undefined);
            }
        };

        axios.post(url, data, config())
            .then(then)
            .catch(promiseHttpErrorReject(reject));
    });
};

export default {
    requestMultiple,
    requestSingle,
    requestByURL
};

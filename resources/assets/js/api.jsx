import axios from 'axios';
import store from './store/store';

let config = () => ({
    headers: {
        'X-CSRF-TOKEN': store.state.token,
        'X-Requested-With': 'XMLHttpRequest'
    }
});

/**
 * Request object base
 * @param params Request parameters
 * @param thenCallback Callback for what to do with the result
 * @constructor
 */
let Request = (params, thenCallback) => ({
    fire(includeGlobal = true) {
        let _params = params;

        if (includeGlobal)
            _params['global'] = {};

        let data = {
            api: JSON.stringify(_params)
        };

        let _then = response => {
            if (includeGlobal) {
                let global = response.data['global'].result;
                store.commit('global', global);
            }

            thenCallback(response);
        };

        axios.post('/api', data, config()).then(_then).catch(error => {
            alert(JSON.stringify(error.response.data.message, null, 2));
        });
    }
});

let URLRequest = (url) => {
    let successCallbacks = [];
    let errorCallbacks = [];
    let thenCallbacks = [];

    /**
     * Define a callback for what to do when the request results with a success
     * @param callback
     * @returns {SingleRequest} this, for chaining
     */
    function success(callback) {
        successCallbacks.push(callback);
        return this;
    }

    /**
     * Define a callback for what to do when the request results with an error
     * @param callback
     * @returns {SingleRequest} this, for chaining
     */
    function error(callback) {
        errorCallbacks.push(callback);
        return this;
    }

    /**
     * Define a callback for what to do after the whole API call is made
     * @param callback
     * @returns {SingleRequest} this, for chaining
     */
    function then(callback) {
        thenCallbacks.push(callback);
        return this;
    }

    let _then = response => {

        let globalDone = false;
        let localDone = false;
        for (let [key, value] of Object.entries(response.data)) {
            if (key === 'global') {
                store.commit('global', value.result);
                globalDone = true;
            } else {
                if (globalDone && localDone) break;
                else if (localDone) continue;

                let callbacks = value.success ? successCallbacks : errorCallbacks;

                for (let c of callbacks)
                    c(value.result);

                for (let c of thenCallbacks)
                    c(response.data);

                localDone = true;
            }
        }
    };

    function fire() {
        axios.post(url, {}, config()).then(_then).catch(error => {
            if (error.response === undefined) {
                alert('Unknown POST request error');
                return;
            }
            alert(JSON.stringify(error.response.data.message, null, 2));
        });
    }

    return {
        success: success,
        error: error,
        then: then,
        fire: fire,
    }
};

/**
 * A single request for the API
 * @param name Request name
 * @param params Request parameters
 * @constructor
 */
let SingleRequest = (name, params) => {
    let successCallbacks = [];
    let errorCallbacks = [];
    let thenCallbacks = [];

    /**
     * Define a callback for what to do when the request results with a success
     * @param callback
     * @returns {SingleRequest} this, for chaining
     */
    function success(callback) {
        successCallbacks.push(callback);
        return this;
    }

    /**
     * Define a callback for what to do when the request results with an error
     * @param callback
     * @returns {SingleRequest} this, for chaining
     */
    function error(callback) {
        errorCallbacks.push(callback);
        return this;
    }

    /**
     * Define a callback for what to do after the whole API call is made
     * @param callback
     * @returns {SingleRequest} this, for chaining
     */
    function then(callback) {
        thenCallbacks.push(callback);
        return this;
    }

    let _then = (response) => {
        let data = response.data[name];
        let callbacks = data.success ? successCallbacks : errorCallbacks;

        for (let c of callbacks)
            c(data.result);

        for (let c of thenCallbacks)
            c(response.data);
    };

    if (!params)
        params = {};

    let fullParams = {};
    fullParams[name] = params;

    let request = Request(fullParams, _then);

    return {
        ...request,
        success: success,
        error: error,
        then: then,
        getName: () => name,
        getParams: () => params,
        getSuccessCallbacks: () => successCallbacks,
        getErrorCallbacks: () => errorCallbacks,
        getThenCallbacks: () => thenCallbacks,
    };
};

/**
 * A combination of requests executed with a single API call
 * @constructor
 */
let CompositeRequest = () => {
    let requests = [];
    let params = {};
    let thenCallbacks = [];
    let errorCallbacks = [];

    /**
     * Add a single request to this composite request
     * @param {SingleRequest} request
     * @returns {CompositeRequest} this, for chaining
     */
    function ask(request) {
        requests.push(request);
        params[request.getName()] = request.getParams();

        return this;
    }

    /**
     * Define a callback for what to do after the whole API call is made
     * @param callback
     * @returns {CompositeRequest} this, for chaining
     */
    function then(callback) {
        thenCallbacks.push(callback);
        return this;
    }

    /**
     * Define an error callback that will be called individually for every request that returns an error.
     * @param callback
     * @returns {CompositeRequest} this, for chaining
     */
    function error(callback) {
        errorCallbacks.push(callback);
        return this;
    }

    let _then = (response) => {
        for (let request of requests) {
            let name = request.getName();
            let data = response.data[name];
            let callbacks = data.success ? request.getSuccessCallbacks() : request.getErrorCallbacks();
            let childThenCallbacks = request.getThenCallbacks();

            for (let c of callbacks)
                c(data.result);

            if (!data.success) {
                for (let c of errorCallbacks)
                    c(name, data.result);
            }

            for (let c of childThenCallbacks)
                c(response.data);
        }

        for (let c of thenCallbacks)
            c(response.data);
    };

    let request = Request(params, _then);

    return {
        ...request,
        ask: ask,
        then: then,
        error: error
    };
};

export default {
    CompositeRequest: CompositeRequest,
    SingleRequest: SingleRequest,
    URLRequest: URLRequest,
};
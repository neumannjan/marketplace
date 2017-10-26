import axios from 'axios';
import store from './store/store';

let config = () => ({
    headers: {
        'X-CSRF-TOKEN': store.state.token,
        'X-Requested-With': 'XMLHttpRequest'
    }
});

let Request = (params, thenCallback) => ({
    fire() {
        let data = {
            api: JSON.stringify(params)
        };

        axios.post('/api', data, config()).then(thenCallback);
    }
});

let SingleRequest = (name, params) => {
    let successCallback = null;
    let errorCallback = null;

    function success(callback) {
        successCallback = callback;
        return this;
    }

    function error(callback) {
        errorCallback = callback;
        return this;
    }

    let _then = (response) => {
        let data = response.data[name];
        let callback = data.success ? successCallback : errorCallback;

        if (callback)
            callback(data.result);
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
        getName: () => name,
        getParams: () => params,
        getSuccessCallback: () => successCallback,
        getErrorCallback: () => errorCallback,
    };
};

let CompositeRequest = () => {
    let requests = [];
    let params = {};
    let thenCallback = null;
    let errorCallback = null;

    function ask(request) {
        requests.push(request);
        params[request.getName()] = request.getParams();

        return this;
    }

    function then(callback) {
        thenCallback = callback;

        return this;
    }

    function error(callback) {
        errorCallback = callback;

        return this;
    }

    let _then = (response) => {
        for (let request of requests) {
            let name = request.getName();
            let data = response.data[name];
            let callback = data.success ? request.getSuccessCallback() : request.getErrorCallback();

            if (callback)
                callback(data.result);

            if (!data.success && errorCallback)
                errorCallback(name, data.result);
        }

        if (thenCallback)
            thenCallback()
    };

    let request = Request(params, _then);

    return {
        ...request,
        ask: ask,
        then: then,
        error: error
    };
};

// default function
export default {
    CompositeRequest: CompositeRequest,
    SingleRequest: SingleRequest
};
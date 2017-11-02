export default {
    data: () => ({
        $api: {
            pending: false,
            fetched: false,
            response: null,
            validationDone: false,
        }
    }),
    methods: {
        $apiCall(request, includeGlobal = true) {
            this.$data.$api.pending = true;
            this.$data.$api.response = null;

            request.then((response) => {
                this.$data.$api.response = response;
                this.$data.$api.pending = false;
                this.$data.$api.fetched = true;
                this.$data.$api.validationDone = false;
            });

            return request;
        }
    }
}

export let apiValidator = (requestName, validationName) => ({
    api(value) {
        if (value === '') return true;

        let api = this.$data.$api;

        if (!api)
            return false;

        if (api.pending || !api.fetched || api.validationDone)
            return true;

        api.validationDone = true;

        if (api.response === null)
            return false;

        let single = api.response[requestName];

        return (single.success || !single.result.validation || !single.result.validation[validationName]);


    }
});
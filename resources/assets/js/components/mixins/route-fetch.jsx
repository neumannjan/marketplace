import router, {events} from 'JS/router';

export default (fetchAsyncFunction, nullObj, before = true) => {

    async function handleResult(vm, result) {
        function setValues(from) {
            for (let [key, value] of Object.entries(from))
                vm.$data[key] = value;
        }

        setValues(nullObj);
        await vm.$nextTick();

        if (result !== undefined) {
            setValues(result);
            await vm.$nextTick();
        }

        events.$emit('loaded');
    }

    function notifyLoading() {
        if (before)
            events.$emit('loading');
    }

    return {
        beforeRouteEnter(to, from, next) {
            if (before) {
                notifyLoading();
                fetchAsyncFunction(to.params).then(result => {
                    next(vm => {
                        handleResult(vm, result);
                    });
                });
            } else {
                next();
            }
        },
        beforeRouteUpdate(to, from, next) {
            if (router.routesMatch(to, from)) {
                next();
                return;
            }

            notifyLoading();
            fetchAsyncFunction(to.params).then(result => {
                if (!before) next();
                handleResult(this, result);
                if (before) next();
            });
        },
        created() {
            let fetchLater = false;

            if (before) {
                for (let matched of this.$route.matched) {
                    for (let instance of Object.values(matched.instances)) {
                        if (instance === this) {
                            fetchLater = true;
                            break;
                        }
                    }
                }
            }

            if (!fetchLater) {
                notifyLoading();
                this.doFetch();
            }
        },
        methods: {
            doFetch() {
                fetchAsyncFunction(this).then(result => handleResult(this, result));
            }
        }
    };
}
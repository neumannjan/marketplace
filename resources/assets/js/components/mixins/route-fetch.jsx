import router, {events} from 'JS/router';

async function handleResult(vm, result, nullObj) {
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

function preFetch() {
    events.$emit('loading');
}

export default (fetchAsyncFunction, nullObj) => ({
    beforeRouteEnter(to, from, next) {
        preFetch();
        fetchAsyncFunction(to.params).then(result => {
            next(vm => {
                handleResult(vm, result, nullObj);
            });
        });

    },
    beforeRouteUpdate(to, from, next) {
        if (router.routesMatch(to, from)) {
            next();
            return;
        }

        preFetch();
        fetchAsyncFunction(to.params).then(result => {
            handleResult(this, result, nullObj);
            next();
        });
    },
    created() {
        let willExecuteRouteEvent = false;
        for (let matched of this.$route.matched) {
            for (let instance of Object.values(matched.instances)) {
                if (instance === this) {
                    willExecuteRouteEvent = true;
                    break;
                }
            }
        }

        if (!willExecuteRouteEvent) {
            preFetch();
            fetchAsyncFunction(this.$route.params).then(result => handleResult(this, result, nullObj));
        }
    }
})
export default (name, get, goTo = {name: 'index'}) => {
    name = `guard_${name}`;

    return {
        watch: {
            [name](val) {
                if (val !== true) {
                    this.$router.push(goTo);
                }
            }
        },
        beforeRouteEnter(to, from, next) {
            next(vm => {
                if (vm[name] !== true)
                    vm.$router.push(goTo);
            });
        },

        beforeRouteUpdate(to, from, next) {
            if (this[name] === true)
                next();
            else
                next(goTo);
        },
        computed: {
            [name]() {
                return get(this);
            }
        }
    };
};
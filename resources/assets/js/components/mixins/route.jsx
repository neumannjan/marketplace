import router from 'JS/router';

const determineActive = (instance, to) => {
    return instance === router.getRouteMainComponent();
};

const changeTitle = (instance, to = null) => {
    if (instance.$data._isMainRoute)
        document.title = (instance.title !== undefined) ? instance.title : to.meta.title;
};

const putScroll = (instance) => {
    if (instance.$data._isMainRoute) {
        instance.$data._scrollX = window.scrollX;
        instance.$data._scrollY = window.scrollY;
    }
};

const retrieveScroll = (instance) => {
    if (instance.$data._isMainRoute) {
        window.scroll(instance.$data._scrollX, instance.$data._scrollY);
    }
};

export default {
    data: () => ({
        _scrollX: 0,
        _scrollY: 0,
        _isMainRoute: false,
    }),
    watch: {
        title() {
            changeTitle(this);
        }
    },
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.$data._isMainRoute = determineActive(vm, to);
            changeTitle(vm, to);
        });
    },
    beforeRouteUpdate(to, from, next) {
        this.$data._isMainRoute = determineActive(this, to);
        putScroll(this);
        changeTitle(this, to);
        next();
    },
    beforeRouteLeave(to, from, next) {
        putScroll(this);
        next();
    },
    activated() {
        this.$nextTick(() => retrieveScroll(this));
    }
}
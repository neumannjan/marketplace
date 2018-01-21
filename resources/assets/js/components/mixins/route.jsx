import router from 'JS/router';

let determineActive = (instance, to) => {
    return instance === router.getCurrentRouteMainComponent();
};

let changeTitle = (instance, to = null) => {
    if (instance.isMainRoute)
        document.title = (instance.title !== undefined) ? instance.title : to.meta.title;
};

export default {
    data: () => ({
        scrollX: 0,
        scrollY: 0,
        isMainRoute: false,
    }),
    watch: {
        title() {
            changeTitle(this);
        }
    },
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.isMainRoute = determineActive(vm, to);
            changeTitle(vm, to);
        });
    },
    beforeRouteUpdate(to, from, next) {
        this.isMainRoute = determineActive(this, to);
        changeTitle(this, to);
        next();
    },
    beforeRouteLeave(to, from, next) {
        if (this.isMainRoute) {
            this.scrollX = window.scrollX;
            this.scrollY = window.scrollY;
        }
        next();
    },
    activated() {
        if (this.isMainRoute) {
            window.scroll(this.scrollX, this.scrollY);
        }
    }
}
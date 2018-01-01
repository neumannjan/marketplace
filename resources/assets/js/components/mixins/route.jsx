let changeTitle = (instance, to) => {
    document.title = (instance.title !== undefined) ? instance.title : to.meta.title;
};

export default {
    data: () => ({
        scrollX: 0,
        scrollY: 0,
    }),
    watch: {
        title(to) {
            changeTitle(this, to);
        }
    },
    beforeRouteEnter(to, from, next) {
        next(vm => {
            changeTitle(vm, to);
        });
    },
    beforeRouteUpdate(to, from, next) {
        changeTitle(this, to);
        next();
    },
    beforeRouteLeave(to, from, next) {
        this.scrollX = window.scrollX;
        this.scrollY = window.scrollY;
        next();
    },
    activated() {
        window.scroll(this.scrollX, this.scrollY);
    }
}
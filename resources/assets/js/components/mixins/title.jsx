let changeTitle = (instance, to) => {
    document.title = (instance.title !== undefined) ? instance.title : to.meta.title;
};

export default {
    beforeRouteEnter(to, from, next) {
        next(vm => {
            changeTitle(vm, to);
        });
    },
    beforeRouteUpdate(to, from, next) {
        changeTitle(this, to);
        next();
    }
}
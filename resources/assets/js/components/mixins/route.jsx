import debounce from 'lodash/debounce';

let changeTitle = (instance, to) => {
    document.title = (instance.title !== undefined) ? instance.title : to.meta.title;
};

export default {
    data: () => ({
        scrollX: null,
        scrollY: null,
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
    activated() {
        const scrollX = this.scrollX;
        const scrollY = this.scrollY;

        if (scrollX === null || scrollY === null)
            return;

        const onScroll = debounce(() => {
            window.scroll(scrollX, scrollY);
            console.log(`wanted: ${scrollY}, got: ${window.scrollY}`);

            window.removeEventListener('scroll', onScroll);
        }, 100);

        window.addEventListener('scroll', onScroll);
    },
    deactivated() {
        this.scrollX = window.scrollX;
        this.scrollY = window.scrollY;
    }
}
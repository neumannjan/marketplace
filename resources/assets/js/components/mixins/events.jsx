const add = (e) => e.target.addEventListener(e.event, e.listener);
const remove = (e) => e.target.removeEventListener(e.event, e.listener);

export default {
    data: () => ({
        events: [],
        vueActive: true,
    }),
    methods: {
        $onJS(target, event, listener) {
            const e = {target, event, listener};

            if (this.vueActive) {
                add(e);
            }

            this.events.push(e);
        }
    },
    activated() {
        this.vueActive = true;
        for (let event of this.events)
            add(event);
    },
    deactivated() {
        this.vueActive = false;
        for (let event of this.events)
            remove(event);
    },
    destroyed() {
        this.vueActive = false;
        for (let event of this.events)
            remove(event);
        this.events = null;
    }
}
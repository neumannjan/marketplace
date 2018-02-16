import ResizeSensor from 'css-element-queries/src/ResizeSensor';
import echo from 'JS/echo';
import {events as appEvents} from 'JS/app';

export default {
    data: () => ({
        events: [],
        vueActive: true,
    }),
    methods: {
        $attachEvent(add, remove) {
            if (this.vueActive)
                add();

            this.events.push([add, remove]);
        },
        $onJS(target, event, listener) {
            this.$attachEvent(
                () => target.addEventListener(event, listener),
                () => target.removeEventListener(event, listener)
            );
        },
        $onElResize(el, listener) {
            this.$attachEvent(
                () => el ? new ResizeSensor(el, listener) : false,
                () => el ? ResizeSensor.detach(el, listener) : false
            );
        },
        $onVue(vm, event, listener) {
            this.$attachEvent(
                () => vm.$on(event, listener),
                () => vm.$off(event, listener)
            );
        },
        $onAppEvents(event, listener) {
            this.$onVue(appEvents, event, listener);
        },
        $onEchoGlobal(event, listener) {
            this.$attachEvent(
                () => echo.global.on(event, listener),
                () => echo.global.off(event, listener)
            );
        },
        $onEcho(type, channel, event, listener) {
            const channelListener = echo.channel(type, channel);
            this.$attachEvent(
                () => channelListener.on(event, listener),
                () => channelListener.off(event, listener)
            );
        },
        $onEchoWhisper(type, channel, event, listener) {
            const channelListener = echo.channel(type, channel);
            this.$attachEvent(
                () => channelListener.onWhisper(event, listener),
                () => channelListener.offWhisper(event, listener)
            );
        },
        $onDocumentVisibility(listener) {
            let hidden, visibilityChange;

            if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
                hidden = "hidden";
                visibilityChange = "visibilitychange";
            } else if (typeof document.msHidden !== "undefined") {
                hidden = "msHidden";
                visibilityChange = "msvisibilitychange";
            } else if (typeof document.webkitHidden !== "undefined") {
                hidden = "webkitHidden";
                visibilityChange = "webkitvisibilitychange";
            }

            const l = () => listener(document[hidden]);

            this.$attachEvent(
                () => {
                    l();
                    return document.addEventListener(visibilityChange, l);
                },
                () => document.removeEventListener(visibilityChange, l)
            );
        }
    },
    activated() {
        this.vueActive = true;
        for (let [add] of this.events)
            add();
    },
    deactivated() {
        this.vueActive = false;
        for (let [add, remove] of this.events)
            remove();
    },
    destroyed() {
        this.vueActive = false;
        for (let [add, remove] of this.events)
            remove();
        this.events = null;
    }
}
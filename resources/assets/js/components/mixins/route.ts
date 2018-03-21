import router, {getRouteMainComponent} from 'JS/router';
import {Route, RawLocation} from "vue-router";
import { Vue, Component, Watch } from "vue-property-decorator";

function determineActive(instance: Vue) {
    return instance === getRouteMainComponent();
}

/**
 * Change the title based on a Vue route component and/or route metadata
 * @param {VM} instance
 * @param {Route} to
 */
function changeTitle(instance: Vue & {title?: string}, to?: Route) {
    if (instance.$data._isMainRoute) {
        if(instance.title) {
            document.title = instance.title;
        } else if(to) {
            document.title = to.meta.title;
        }
    }
}

/**
 * Save current scroll position
 * @param {VM} instance
 */
function putScroll(instance: RouteMixin) {
    if (instance.$data._isMainRoute) {
        instance.$data._scrollX = window.scrollX;
        instance.$data._scrollY = window.scrollY;
    }
}

/**
 * Scroll to saved scroll position
 * @param {VM} instance
 */
function retrieveScroll(instance: RouteMixin) {
    if (instance.$data._isMainRoute) {
        window.scroll(instance.$data._scrollX, instance.$data._scrollY);
    }
}

@Component({})
export default class RouteMixin extends Vue {
    /**
     * Saved horizontal scroll position
     */
    _scrollX: number = 0;

    /**
     * Saved vertical scroll position
     */
    _scrollY: number = 0;

    /**
     * Whether the Vue component is the main route component of the current route
     */
    _isMainRoute: boolean = false;

    @Watch('title')
    onTitleChanged() {
        changeTitle(this);
    }

    beforeRouteEnter(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
        next((vm: Vue) => {
            vm.$data._isMainRoute = determineActive(vm);
            changeTitle(vm, to);
        });
    }

    beforeRouteUpdate(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
        this.$data._isMainRoute = determineActive(this);
        putScroll(this);
        changeTitle(this, to);
        next();
    }

    beforeRouteLeave(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
        putScroll(this);
        next();
    }

    activated() {
        this.$nextTick(() => retrieveScroll(this));
    }
};
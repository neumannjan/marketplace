import router, {getRouteMainComponent} from 'JS/router';
import {Route, RawLocation, RouteRecord} from "vue-router";
import { Vue, Component, Watch } from "JS/components/class-component";

function determineActive(instance: Vue) {
    return instance === getRouteMainComponent();
}

/**
 * Change the title based on a Vue route component and/or route metadata
 * @param {VM} instance
 * @param {Route} to
 */
function changeTitle(instance: Vue & {title?: string}) {
    const route = instance.$route;
    const matched = route.matched.slice(0);
    
    if (matched.length === 0) {
        return;
    }

    if(!instance.$data._isMainRoute)
        return;

    let title = null;

    for(let i = matched.length - 1; i >= 0; --i) {
        const inst = matched[i].instances.default;
        if(inst && (<any>inst).title) {
            if(title)
                title = `${(<any>inst).title} | ${title}`;
            else
                title = (<any>inst).title;

        }
    }

    if(title)
        document.title = title;
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
            changeTitle(vm);
        });
    }

    beforeRouteUpdate(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
        this.$data._isMainRoute = determineActive(this);
        putScroll(this);
        changeTitle(this);
        next();
    }

    beforeRouteLeave(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: Vue) => any) | void) => void) {
        putScroll(this);
        next();
    }

    created() {
        this.$data._isMainRoute = determineActive(this);
        changeTitle(this);
    }

    activated() {
        this.$data._isMainRoute = determineActive(this);
        changeTitle(this);
        this.$nextTick(() => retrieveScroll(this));
    }
};
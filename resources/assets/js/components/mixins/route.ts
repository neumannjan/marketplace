import router, {getRouteMainComponent} from 'JS/router';
import Vue from "vue";
import {Route} from "vue-router";

interface RouteMixinData {
    /**
     * Saved horizontal scroll position
     */
    _scrollX: number,

    /**
     * Saved vertical scroll position
     */
    _scrollY: number,

    /**
     * Whether the Vue component is the main route component of the current route
     */
    _isMainRoute: boolean,
}

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

const vm = Vue.extend({
    data: (): RouteMixinData => ({
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
        next((vm: Vue) => {
            vm.$data._isMainRoute = determineActive(vm);
            changeTitle(vm, to);
        });
    },
    beforeRouteUpdate(to, from, next) {
        this.$data._isMainRoute = determineActive(this);
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
});

/**
 * Save current scroll position
 * @param {VM} instance
 */
function putScroll(instance: RouteMixinData & Vue) {
    if (instance.$data._isMainRoute) {
        instance.$data._scrollX = window.scrollX;
        instance.$data._scrollY = window.scrollY;
    }
}

/**
 * Scroll to saved scroll position
 * @param {VM} instance
 */
function retrieveScroll(instance: RouteMixinData & Vue) {
    if (instance.$data._isMainRoute) {
        window.scroll(instance.$data._scrollX, instance.$data._scrollY);
    }
}

export default vm;
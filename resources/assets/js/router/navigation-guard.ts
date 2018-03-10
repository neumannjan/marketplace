import {NavigationGuard as _NavigationGuard} from "vue-router";

export interface NavigationGuard {
    beforeEnter: _NavigationGuard
}

export default NavigationGuard;
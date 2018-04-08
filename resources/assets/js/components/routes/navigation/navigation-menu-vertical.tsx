import Vue from "vue";
import {Location} from "vue-router";

import 'vue-awesome/icons/flag';
import 'vue-awesome/icons/ban';
import router, {routesMatch} from "JS/router";

export interface VerticalButton {
    icon: string,
    label: string,
    location: Location
}

export default Vue.extend({
    name: "navigation-menu-vertical",
    functional: true,
    props: {
        buttons: {
            type: Array,
            required: true
        }
    },
    render(h, context) {
        return (
            <ul {...context.data} class="nav flex-column">
                {context.props.buttons.map((b: VerticalButton) => (
                    <li class="nav-item">
                        <router-link to={b.location} class={[
                            'nav-link btn-link-gray',
                        ]} active-class="active">
                            <icon name={b.icon} class="mr-3"/>
                            <span>{b.label}</span>
                        </router-link>
                    </li>
                ))}
            </ul>
        );
    }
});
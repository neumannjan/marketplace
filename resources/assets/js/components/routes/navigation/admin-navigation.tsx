import Vue from "vue";
import { Location } from "vue-router";

import 'vue-awesome/icons/flag';
import 'vue-awesome/icons/ban';
import router, { routesMatch } from "JS/router";

interface Button {
    icon: string,
    label: string,
    location: Location
}

export default Vue.extend({
    name: "admin-navigation",
    functional: true,
    render(h) {
        const buttons: Button[] = [
            {
                icon: 'flag',
                label: 'Reported offers',
                location: {
                    name: 'admin-reported'
                }
            },
            {
                icon: 'ban',
                label: 'Banned users',
                location: {
                    name: 'admin-banned'
                }
            }
        ];

        return (
            <div class="p-4">
                <h1 class="h2 text-center">Administration</h1>
                <ul class="nav flex-column">
                    {buttons.map(b => (
                        <li class="nav-item">
                            <router-link to={b.location} class={[
                                'nav-link btn-link-gray',
                                {'active': routesMatch(b.location, router.currentRoute)}
                            ]}>
                                <icon name={b.icon} class="mr-3" />
                                <span>{b.label}</span>
                            </router-link>
                        </li>
                    ))}
                </ul>
            </div>
        );
    }
});
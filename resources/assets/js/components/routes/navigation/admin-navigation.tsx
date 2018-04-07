import Vue from "vue";

import 'vue-awesome/icons/flag';
import 'vue-awesome/icons/ban';
import store from "JS/store";
import NavigationMenuVertical, {VerticalButton} from './navigation-menu-vertical';

export default Vue.extend({
    name: "admin-navigation",
    components: {
        NavigationMenuVertical
    },
    render(h) {
        const buttons: VerticalButton[] = [
            {
                icon: 'flag',
                label: store.getters.trans('interface.page.reported'),
                location: {
                    name: 'admin-reported'
                }
            },
            {
                icon: 'ban',
                label: store.getters.trans('interface.page.banned'),
                location: {
                    name: 'admin-banned'
                }
            }
        ];

        return (
            <div class="p-4">
                <h1 class="h2 text-center">{store.getters.trans('interface.page.admin')}</h1>
                <navigation-menu-vertical buttons={buttons}/>
            </div>
        );
    }
});
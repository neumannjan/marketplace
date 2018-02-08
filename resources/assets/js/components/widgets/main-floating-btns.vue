<template>
    <div>
        <floating-btns :buttons="buttons" @click="click"/>
        <popper v-if="popperEl" :element="popperEl" placement="left-start">
            <div class="card mr-3 mb-1" :style="{ width: '300px', height: '400px' }">
                <template v-if="lastSelectedButton === 'chat'">
                    <div class="card-body">
                        <h5 class="card-title">Chat</h5>
                    </div>
                </template>
                <template v-if="lastSelectedButton === 'notifications'">
                    <div class="card-body">
                        <h5 class="card-title">Notifications</h5>
                    </div>
                </template>
            </div>
        </popper>
    </div>
</template>

<script>
import FloatingBtns from './floating-btns';
import Popper from './popper';
import events from 'JS/components/mixins/events';
import router from 'JS/router';

import 'vue-awesome/icons/plus';
import 'vue-awesome/icons/comment';
import 'vue-awesome/icons/bell';
import 'vue-awesome/icons/angle-left';
import 'vue-awesome/icons/angle-up';

const BTN_ADD = 'add';
const BTN_CHAT = 'chat';
const BTN_NOTIFICATIONS = 'notifications';
const BTN_BACK = 'back';
const BTN_UP = 'up';

//TODO labels
const buttons = [
    {id: BTN_ADD, icon: 'plus', class: 'btn-primary', show: () => router.currentRoute.name !== 'offer-create'},
    {id: BTN_CHAT, icon: 'comment'},
    {id: BTN_NOTIFICATIONS, icon: 'bell'},
];

const backButton = {id: BTN_BACK, icon: 'angle-left'};
const upButton = {id: BTN_UP, icon: 'angle-up', class: 'btn-danger'};

export default {
    name: 'main-floating-btns',
    components: {
        FloatingBtns,
        Popper
    },
    mixins: [events],
    data: () => ({
        buttons: [],
        scrollY: window.scrollY,
        backShown: false,
        popperEl: null,
        lastSelectedButton: null,
    }),
    watch: {
        '$route'() {
            this.scrollY = window.scrollY;
            this.backShown = !router.getRouteMainComponent().isTopLevelRoute;
            this.buttons = this.aButtons;
        },
        buttons() {
            if (this.popperEl && !this.buttons.map(b => b.id).includes(this.lastSelectedButton)) {
                this.popperEl = null;
            }
        }
    },
    computed: {
        aButtons() {
            if (this.$store.state.is_authenticated)
                return this.backShown ? [...buttons, backButton] : buttons;
            else
                return this.backShown ? [backButton] : [];

        },
        bButtons() {
            return [upButton];
        }
    },
    methods: {
        click(button, element) {
            const lastPopperEl = this.popperEl;
            this.popperEl = null;
            this.lastSelectedButton = button.id;

            switch (button.id) {
            case BTN_ADD:
                router.push({name: 'offer-create'});
                break;
            case BTN_CHAT:
            case BTN_NOTIFICATIONS:
                if(lastPopperEl === element)
                    this.popperEl = null;
                else
                    this.popperEl = element;
                break;
            case BTN_BACK:
                router.back();
                break;
            case BTN_UP:
                window.scrollTo(window.scrollX, 0);
                break;
            }
        }
    },
    mounted() {
        this.$onJS(window, 'scroll', () => {
            const scrollY = window.scrollY;

            if (scrollY <= 300 || scrollY > this.scrollY) {
                this.buttons = this.aButtons;
            } else {
                this.buttons = this.bButtons;
            }

            this.scrollY = scrollY;
        });

        this.buttons = this.aButtons;
    },
};
</script>

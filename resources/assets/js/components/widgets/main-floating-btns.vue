<template>
    <div class="fixed-bottom-right">
        <floating-btns :buttons="buttons" @click="click"/>
        <popper v-if="popperEl" :element="popperEl" placement="left-start" class="card-popup-z">
            <chat-card v-if="lastSelectedButton === 'chat'" class="card-popup mr-3 m-1" @close="popperEl = null"/>
            <notifications-card v-else-if="lastSelectedButton === 'notifications'" class="card-popup mr-3 m-1"/>
        </popper>
    </div>
</template>

<script>
    import FloatingBtns from './floating-btns';
    import Popper from './popper';
    import events from 'JS/components/mixins/events';
    import router from 'JS/router';
    import {mapState} from 'vuex';

    import 'vue-awesome/icons/plus';
    import 'vue-awesome/icons/comment';
    import 'vue-awesome/icons/bell';
    import 'vue-awesome/icons/angle-left';
    import 'vue-awesome/icons/angle-up';
    import ProfileImg from "JS/components/widgets/image/profile-img";
    import ChatCard from "JS/components/widgets/card/chat-card";
    import NotificationsCard from "JS/components/widgets/card/notifications-card";

    const BTN_ADD = 'add';
    const BTN_CHAT = 'chat';
    const BTN_NOTIFICATIONS = 'notifications';
    const BTN_BACK = 'back';
    const BTN_UP = 'up';

    //TODO labels
    const mainButtons = [
        {id: BTN_ADD, icon: 'plus', class: 'btn-primary', show: () => router.currentRoute.name !== 'offer-create'},
        {id: BTN_CHAT, icon: 'comment', websocketRequired: true, httpRequired: true},
        {id: BTN_NOTIFICATIONS, icon: 'bell'},
    ];

    const backButton = {id: BTN_BACK, icon: 'angle-left'};
    const upButton = {id: BTN_UP, icon: 'angle-up', class: 'btn-danger'};

    export default {
        name: 'main-floating-btns',
        components: {
            NotificationsCard,
            ChatCard,
            ProfileImg,
            FloatingBtns,
            Popper
        },
        mixins: [events],
        data: () => ({
            isSideA: true,
            scrollY: window.scrollY,
            backShown: false,
            popperEl: null,
            lastSelectedButton: null,
        }),
        watch: {
            '$route'() {
                this.scrollY = window.scrollY;
                this.backShown = !router.getRouteMainComponent().isTopLevelRoute;
                this.isSideA = true;
            },
            buttons(buttons) {
                if (this.popperEl && !buttons.map(b => b.id).includes(this.lastSelectedButton)) {
                    this.popperEl = null;
                }
            }
        },
        computed: {
            buttons() {
                let buttons = null;

                if (this.isSideA) {
                    if (this.isAuthenticated) {
                        buttons = this.backShown ? [...mainButtons, backButton] : mainButtons;
                    } else {
                        buttons = this.backShown ? [backButton] : [];
                    }
                } else {
                    buttons = [upButton];
                }

                return buttons.filter(b => (b.websocketRequired !== true || this.websocketConnection)
                    && (b.httpRequired !== true || this.httpConnection));
            },
            ...mapState({
                isAuthenticated: state => state.is_authenticated,
                httpConnection: state => state.connection_http !== null ? state.connection_http : true,
                websocketConnection: state => state.connection_websocket !== null ? state.connection_websocket : true,
            })
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
                        if (lastPopperEl === element)
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

                this.isSideA = scrollY <= 300 || scrollY > this.scrollY || this.popperEl;

                this.scrollY = scrollY;
            });
        },
    };
</script>

<style scoped lang="scss" type="text/scss">
    .fixed-bottom-right {
        z-index: 1020;
    }

    .card-popup-z {
        z-index: 1030;
    }
</style>
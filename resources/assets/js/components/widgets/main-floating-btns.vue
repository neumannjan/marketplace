<template>
    <div class="fixed-bottom-right">
        <floating-btns :buttons="buttons" :badges="badges" @click="onClick" @buttons="onButtonEls"/>
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
        {id: BTN_CHAT, icon: 'comment'},
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
            buttonEls: null,
            chatConversations: {}
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
                if (this.isSideA) {
                    if (this.isAuthenticated) {
                        return this.backShown ? [...mainButtons, backButton] : mainButtons;
                    } else {
                        return this.backShown ? [backButton] : [];
                    }
                } else {
                    return [upButton];
                }
            },
            badges() {
                return {
                    [BTN_CHAT]: Object.keys(this.chatConversations).length
                }
            },
            ...mapState({
                isAuthenticated: state => state.is_authenticated,
            })
        },
        methods: {
            isOpen(buttonId) {
                return this.popperEl && this.lastSelectedButton === buttonId;
            },
            onClick(button, element) {
                this.click(button.id, element);
            },
            click(buttonId, element = null, toggle = true) {
                const result = (() => {
                    const lastPopperEl = this.popperEl;
                    this.popperEl = null;
                    this.lastSelectedButton = buttonId;

                    switch (buttonId) {
                        case BTN_ADD:
                            router.push({name: 'offer-create'});
                            return true;
                        case BTN_CHAT:
                        case BTN_NOTIFICATIONS:
                            if (element) {
                                if (toggle && lastPopperEl === element)
                                    this.popperEl = null;
                                else
                                    this.popperEl = element;

                                return true;
                            } else if (this.buttonEls !== null && this.buttonEls.length > 0) {
                                this.popperEl = this.buttonEls[0];

                                return true;
                            }
                            return false;
                        case BTN_BACK:
                            router.back();
                            return true;
                        case BTN_UP:
                            window.scrollTo(window.scrollX, 0);
                            return true;
                    }

                    return false;
                })();

                if (result) {
                    switch (buttonId) {
                        case BTN_CHAT:
                            this.chatConversations = {};
                            break;
                    }
                }

                return result;
            },
            onButtonEls(els) {
                this.buttonEls = els;
            }
        },
        mounted() {
            this.$onJS(window, 'scroll', () => {
                const scrollY = window.scrollY;

                this.isSideA = scrollY <= 300 || scrollY > this.scrollY || this.popperEl;

                this.scrollY = scrollY;
            });

            this.$onAppEvents('request-popup', data => {
                if (this.click(data.type, null, false)) {
                    this.$nextTick(data.then);
                }
            });

            this.$onEchoGlobal('MessageSentOther', message => {
                if (!this.isOpen(BTN_CHAT)) {
                    this.$set(this.chatConversations, message.from.username, true);
                }
            });

            this.$onAppEvents('unread_conversations', conversations => {
                const c = {};

                for (let conversation of conversations) {
                    c[conversation.from.username] = true;
                }

                this.chatConversations = c;
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
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
    import FloatingBtns from './floating-btns.vue';
    import Popper from './popper.vue';
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import ChatCard from "JS/components/widgets/card/chat-card.vue";
    import NotificationsCard from "JS/components/widgets/card/notifications-card.vue";

    import events from 'JS/components/mixins/events';
    import appEvents,{ Events } from 'JS/events';
    import router,{ getRouteMainComponent } from 'JS/router';
    import {mapState} from 'vuex';

    import 'vue-awesome/icons/plus';
    import 'vue-awesome/icons/comment';
    import 'vue-awesome/icons/bell';
    import 'vue-awesome/icons/angle-left';
    import 'vue-awesome/icons/angle-up';
import { Message, Conversation } from 'JS/api/types';

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
            /** @type {HTMLElement | null} */
            popperEl: null,
            /** @type {string | null} */
            lastSelectedButton: null,
            /** @type {HTMLElement[] | null} */
            buttonEls: null,
            chatConversations: {}
        }),
        watch: {
            '$route'() {
                this.scrollY = window.scrollY;

                const mainComponent = getRouteMainComponent();
                this.backShown = !mainComponent || !mainComponent.isTopLevelRoute;
                this.isSideA = true;
            },
            buttons(buttons) {
                //@ts-ignore
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
            /**
             * @param {string} buttonId
             */
            isOpen(buttonId) {
                return this.popperEl && this.lastSelectedButton === buttonId;
            },
            /**
             * @param {object & {id: string}} button
             * @param {HTMLElement | null} element
             */
            onClick(button, element) {
                this.click(button.id, element);
            },
            /**
             * @param {string} buttonId
             * @param {HTMLElement | null} element
             * @param {boolean} toggle
             */
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
            /** 
             * @param {HTMLElement[]} els
             */
            onButtonEls(els) {
                this.buttonEls = els;
            }
        },
        mounted() {
            this.$onJS(window, 'scroll', () => {
                const scrollY = window.scrollY;

                this.isSideA = scrollY <= 300 || scrollY > this.scrollY || !!this.popperEl;

                this.scrollY = scrollY;
            });

            /**
             * @param {{ type: string, then: () => void }} data
             */
            const onRequestPopup = data => {
                if (this.click(data.type, null, false)) {
                    this.$nextTick(data.then);
                }
            };

            this.$onEventListener(appEvents, Events.RequestPopup, onRequestPopup);

            /**
             * @param {Message} message
             */
            const onMessageSentOther = message => {
                if (!this.isOpen(BTN_CHAT)) {
                    this.$set(this.chatConversations, message.from.username, true);
                }
            };

            this.$onEventListener(appEvents, Events.MessageSentOther, onMessageSentOther);

            /**
             * @param  {Conversation[]} conversations
             */
            const onUnreadConversations = conversations => {
                const c = {};

                for (let conversation of conversations) {
                    c[conversation.from.username] = true;
                }

                this.chatConversations = c;
            }

            this.$onEventListener(appEvents, Events.UnreadConversations, onUnreadConversations);
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
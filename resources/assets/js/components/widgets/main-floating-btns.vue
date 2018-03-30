<template>
    <div class="fixed-bottom-right">
        <floating-btns :buttons="buttons" :badges="badges" @click="onClick" @buttons="onButtonEls"/>
        <popper v-if="popperEl" :element="popperEl" placement="left-start" class="card-popup-z">
            <chat-card v-if="lastSelectedButton === 'chat'" class="card-popup mr-3 m-1" @close="popperEl = null"/>
            <notifications-card v-else-if="lastSelectedButton === 'notifications'" class="card-popup mr-3 m-1"/>
        </popper>
    </div>
</template>

<script lang="ts">
    import FloatingBtns from './floating-btns.vue';
    import Popper from './popper.vue';
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import ChatCard from "JS/components/widgets/card/chat-card.vue";
    import NotificationsCard from "JS/components/widgets/card/notifications-card.vue";

    import Vue from 'vue';
    import store, { State } from 'JS/store';
    import appEvents,{ Events, RequestPopupPayload } from 'JS/events';
    import router,{ getRouteMainComponent } from 'JS/router';
    import { Message, Conversation } from 'JS/api/types';
    import messaging from 'JS/api/messaging';
    import { NormalizedMessage } from 'JS/api/messaging/typings';

    import 'vue-awesome/icons/plus';
    import 'vue-awesome/icons/comment';
    import 'vue-awesome/icons/bell';
    import 'vue-awesome/icons/angle-left';
    import 'vue-awesome/icons/angle-up';
    import notifications,{ NotificationTypes } from 'JS/notifications';
    import { NotificationEvents } from 'JS/lib/notifications';
    import { Route } from 'vue-router';

    enum ButtonTypes {
        Add = 'add',
        Chat = 'chat',
        Notifications = 'notifications',
        Back = 'back',
        Up = 'up',
    }

    interface Button {
        id: ButtonTypes,
        icon: string,
        class?: string,
        label?: string, //TODO make required
        badgeType?: string,
        show?: (route: Route) => boolean
    }

    type ChatConversations = {
        [index: string]: boolean
    }

    //TODO labels
    const mainButtons: Button[] = [
        {id: ButtonTypes.Add, icon: 'plus', class: 'btn-primary', show: (route: Route) => route.name !== 'offer-create'},
        {id: ButtonTypes.Chat, icon: 'comment'},
        {id: ButtonTypes.Notifications, icon: 'bell'},
    ];

    const backButton = {id: ButtonTypes.Back, icon: 'angle-left'};
    const upButton = {id: ButtonTypes.Up, icon: 'angle-up', class: 'btn-danger'};

    export default Vue.extend({
        name: 'main-floating-btns',
        components: {
            NotificationsCard,
            ChatCard,
            ProfileImg,
            FloatingBtns,
            Popper
        },
        data: (): {
            isSideA: boolean,
            scrollY: number,
            popperEl: HTMLElement | null,
            lastSelectedButton: ButtonTypes | null,
            buttonEls: HTMLElement[] | null,
            chatConversations: ChatConversations
        } => ({
            isSideA: true,
            scrollY: window.scrollY,
            popperEl: null,
            lastSelectedButton: null,
            buttonEls: null,
            chatConversations: {}
        }),
        watch: {
            '$route'() {
                this.scrollY = window.scrollY;
                this.isSideA = true;
            },
            buttons(buttons: Button[]) {
                if (this.popperEl && (!this.lastSelectedButton || !buttons.map(b => b.id).includes(this.lastSelectedButton))) {
                    this.popperEl = null;
                }
            },
            popperEl(val: boolean) {
                notifications.forceHidden(NotificationTypes.NewMessages, val && this.lastSelectedButton === ButtonTypes.Chat);
            }
        },
        computed: {
            async backShown(): Promise<boolean> {
                await this.$nextTick();
                if(store.state.reRoutedTimes <= 0)
                    return false;

                const matched = this.$route.matched;

                if(matched.length === 0)
                    return true;

                const instance = matched[matched.length - 1].instances.default;
                console.log(matched[matched.length - 1].instances.default);
                
                if(!instance)
                    return true;

                return (<any>instance).isTopLevelRoute === false;
            },
            buttons(): Button[] {
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
            badges(): {[type in ButtonTypes]?: number | string} {
                return {
                    [ButtonTypes.Chat]: Object.keys(this.chatConversations).length
                }
            },
            isAuthenticated(): boolean {
                return !!store.state.user;
            }
        },
        methods: {
            isOpen(buttonId: ButtonTypes) {
                return this.popperEl && this.lastSelectedButton === buttonId;
            },
            onClick(button: Button, element: HTMLElement | null) {
                this.click(button.id, element);
            },
            click(buttonId: ButtonTypes, element: HTMLElement | null = null, toggle = true) {
                const result = (() => {
                    const lastPopperEl = this.popperEl;
                    this.popperEl = null;
                    this.lastSelectedButton = buttonId;

                    switch (buttonId) {
                        case ButtonTypes.Add:
                            router.push({name: 'offer-create'});
                            return true;
                        case ButtonTypes.Chat:
                        case ButtonTypes.Notifications:
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
                        case ButtonTypes.Back:
                            router.back();
                            return true;
                        case ButtonTypes.Up:
                            window.scrollTo(window.scrollX, 0);
                            return true;
                    }

                    return false;
                })();

                if (result) {
                    switch (buttonId) {
                        case ButtonTypes.Chat:
                            this.chatConversations = {};
                            break;
                    }
                }

                return result;
            },
            onButtonEls(els: HTMLElement[]) {
                this.buttonEls = els;
            }
        },
        mounted() {
            this.$onJS(window, 'scroll', () => {
                const scrollY = window.scrollY;

                this.isSideA = scrollY <= 300 || scrollY > this.scrollY || !!this.popperEl;

                this.scrollY = scrollY;
            });

            this.$onEventListener(appEvents, Events.RequestPopup, (data: RequestPopupPayload<ButtonTypes>) => {
                if (this.click(data.type, null, false)) {
                    this.$nextTick(data.then);
                }
            });

            this.$onEventListener(appEvents, Events.MessageSent, (message: NormalizedMessage) => {
                if (!this.isOpen(ButtonTypes.Chat)) {
                    this.$set(this.chatConversations, message.from.username, true);
                }
            });

            this.$onEventListener(appEvents, Events.UnreadConversations, (conversations: Conversation[]) => {
                const c: ChatConversations = {};

                for (let conversation of conversations) {
                    c[conversation.from.username] = true;
                }

                this.chatConversations = c;
            });
        },
    });
</script>

<style scoped lang="scss" type="text/scss">
    .fixed-bottom-right {
        z-index: 1020;
    }

    .card-popup-z {
        z-index: 1030;
    }
</style>
<template>
    <div class="wrapper flex-row">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <top-nav class="navbar-nav navbar-nav-fixed-top"/>
            <bottom-nav class="navbar-nav navbar-nav-fixed-bottom"/>
        </div>
        <div class="wrapper">
            <main role="main"
                  :style="loadingStyle"
                  :class="['main', {'navigation-shown': has.navigation, 'navigation-not-shown': !has.navigation}]">
                <!-- NAVIGATION -->
                <div v-if="shown && has.navigation" class="content-navigation">
                    <keep-alive :include="keepAlive('navigation')">
                        <router-view class="content-navigation-inner" name="navigation"/>
                    </keep-alive>
                </div>

                <div class="main-container" v-if="shown || !$route.meta.refreshOnReconnect">
                    <!-- FLASH MESSAGES -->
                    <flash-messages/>

                    <!-- CONTENT -->
                    <keep-alive :include="keepAlive()">
                        <router-view/>
                    </keep-alive>
                </div>
            </main>

            <footer class="footer">
                <div class="footer-inner">
                    <span class="ml-auto">&copy; Company 2018</span> <!--TODO replace-->
                </div>
            </footer>
        </div>

        <main-floating-btns/>
        <modal-router :data="modals" v-if="shown"/>
        <notifications class="fixed-top-right notifications"/>
    </div>
</template>

<script lang="ts">
    import {cached, CachedRouteComponents, routeEvents, queryModalRouter, RouteEvents, QueryModalRouter} from 'JS/router';
    import { Conversation, Message } from 'JS/api/types';
    import {mapState} from 'vuex';
    import appEvents,{ Events } from "JS/events";
    import store, {State} from 'JS/store';
    import Vue from 'vue';
    import notifications,{ NotificationTypes } from 'JS/notifications';
    import { Route } from 'vue-router/types/router';
    import initial from 'JS/store/initial';

    import TopNav from './widgets/nav/vertical/top-nav.vue';
    import BottomNav from './widgets/nav/vertical/bottom-nav.vue';
    import FlashMessages from './widgets/flash-messages.vue';
    import Icon from "../../../../node_modules/vue-awesome/components/Icon.vue";
    import MainFloatingBtns from './widgets/main-floating-btns.vue';
    import Modal from './widgets/modal.vue';
    import ModalRouter from "JS/components/widgets/modal-router.vue";
    import Notifications from "JS/components/widgets/notifications.vue";

    interface Has {
        navigation?: boolean
    }

    export default Vue.extend({
        name: 'app',
        components: {
            Notifications,
            ModalRouter,
            Icon,
            TopNav,
            BottomNav,
            FlashMessages,
            MainFloatingBtns,
            Modal,
        },
        data: (): {
            keepAlive: typeof cached,
            shown: boolean,
            has: Has,
            modals: QueryModalRouter,
            loading: boolean,
        } => ({
            keepAlive: cached,
            shown: true,
            has: {},
            modals: queryModalRouter,
            loading: false,
        }),
        watch: {
            $route(to: Route) {
                if (!to.matched) return;

                const has: Has = {
                    navigation: !!to.matched[0].components['navigation']
                } as Has;

                this.has = has;
            },
            httpConnection(val: State['connection_http'], oldVal: State['connection_http']) {
                if (oldVal !== undefined && oldVal !== null && val !== oldVal) {
                    this.notifyConnection(true, val ? true : false);
                }
            },
            websocketConnection(val: State['connection_websocket'], oldVal: State['connection_websocket']) {
                if (oldVal !== undefined && oldVal !== null && val !== oldVal) {
                    this.notifyConnection(false, val ? true : false);
                }
            }
        },
        computed: {
            ...mapState({
                httpConnection: (state: State) => state.connection_http,
                websocketConnection: (state: State) => state.connection_websocket
            }),
            loadingStyle(): object {
                return this.loading ? {visibility: 'hidden'} : {};
            }
        },
        methods: {
            notifyConnection(isHttp: boolean, value: boolean) {
                const getID = (isHttp: boolean, value: boolean) => {
                    if(isHttp) {
                        return value ? NotificationTypes.HttpConnection : NotificationTypes.NoHttpConnection;
                    } else {
                        return value ? NotificationTypes.WebsocketConnection : NotificationTypes.NoWebsocketConnection;
                    }
                }

                notifications.showNotification({
                    id: getID(isHttp, value),
                    persistent: !value,
                    message: `TODO message: ${isHttp ? 'http' : 'websocket'} connection ${value ? 'regained' : 'lost'}.`,
                    type: value ? 'success' : 'danger'
                });

                notifications.hideNotification(getID(isHttp, !value));

                if (isHttp && value) {
                    this.restoreConnection();
                }
            },
            async restoreConnection() {
                if (!this.shown) return;

                store.commit('httpConnection', true);

                this.shown = false;
                await this.$nextTick();
                this.shown = true;
                await this.$nextTick();
            },
        },
        mounted() {
            this.$onEventListener(routeEvents, RouteEvents.Loading, () => this.loading = true);
            this.$onEventListener(routeEvents, RouteEvents.Loaded, () => this.loading = false);

            const unreadConversations = initial.get('unread_conversations', []);

            if(unreadConversations && unreadConversations.length > 0) {
                appEvents.dispatch(Events.UnreadConversations, unreadConversations);
            }
        },
        created() {
            const addNotification = (amount: number) => {
                //TODO translate
                notifications.showNotification({
                    id: NotificationTypes.NewMessages,
                    message: amount > 1 ? 'You have new messages.' : 'You have a new message.',
                    type: 'primary'
                });
            };

            this.$onEventListener(appEvents, Events.MessageSent, (message: Message) => {
                if(!message.mine) {
                    addNotification(1);
                }
            });

            this.$onEventListener(appEvents, Events.UnreadConversations, (conversations: Conversation[]) => {
                addNotification(conversations.length);
            });
        }
    });
</script>

<style scoped>
    .notifications {
        z-index: 1100;
    }
</style>
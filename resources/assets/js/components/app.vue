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
                <div v-if="(shown || !$route.meta.refreshOnReconnect) && has.navigation" class="content-navigation">
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
                    <span class="ml-auto">&copy; {{copyright}}</span>
                </div>
            </footer>
        </div>

        <main-floating-btns/>
        <modal-router :data="modals" v-if="shown"/>
        <notifications class="fixed-top-right notifications"/>
    </div>
</template>

<script lang="ts">
    import {cached, queryModalRouter, QueryModalRouter, RouteEvents, routeEvents} from 'JS/router';
    import {mapState} from 'vuex';
    import appEvents, {Events} from "JS/events";
    import store, {State} from 'JS/store';
    import Vue from 'vue';
    import notifications, {NotificationTypes} from 'JS/notifications';
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
            modals: QueryModalRouter,
            loading: boolean,
        } => ({
            keepAlive: cached,
            shown: true,
            modals: queryModalRouter,
            loading: false,
        }),
        watch: {
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
            },
            has(): Has {
                if (this.$route.matched.length === 0)
                    return {};

                const has: Has = {
                    navigation: !!this.$route.matched[0].components['navigation']
                } as Has;

                return has;
            },
            copyright(): string {
                return `${this.$store.state.name} ${(new Date()).getFullYear()}`;
            }
        },
        methods: {
            notifyConnection(isHttp: boolean, value: boolean) {
                const getID = (isHttp: boolean, value: boolean) => {
                    if (isHttp) {
                        return value ? NotificationTypes.HttpConnection : NotificationTypes.NoHttpConnection;
                    } else {
                        return value ? NotificationTypes.WebsocketConnection : NotificationTypes.NoWebsocketConnection;
                    }
                }

                notifications.showNotification({
                    id: getID(isHttp, value),
                    persistent: !value,
                    message: store.getters.trans(`interface.connection.${isHttp ? 'http' : 'websocket'}.${value ? 'gained' : 'lost'}`),
                    type: value ? 'success' : 'danger'
                });

                notifications.hideNotification(getID(isHttp, !value));

                if (isHttp && value) {
                    this.restoreConnection();
                }
            },
            restoreConnection() {
                if (!this.shown) return;

                store.commit('httpConnection', true);

                this.refresh();
            },
            async refresh() {
                this.shown = false;
                await this.$nextTick();
                this.shown = true;
                await this.$nextTick();
                appEvents.dispatch(Events.AfterAppRefresh, undefined);
            }
        },
        mounted() {
            this.$onEventListener(routeEvents, RouteEvents.Loading, () => this.loading = true);
            this.$onEventListener(routeEvents, RouteEvents.Loaded, () => this.loading = false);

            const unreadConversations = initial.get('unread_conversations', []);

            if (unreadConversations && unreadConversations.length > 0) {
                appEvents.dispatch(Events.UnreadConversations, unreadConversations);
            }
        },
        created() {
            const addNotification = (amount: number) => {
                notifications.showNotification({
                    id: NotificationTypes.NewMessages,
                    message: store.getters.transChoice('interface.notification.messages', amount, {
                        amount: amount.toString()
                    }),
                    type: 'primary'
                });
            };

            this.$onEventListener(appEvents, Events.MessageSent, message => {
                if (!message.mine) {
                    addNotification(1);
                }
            });

            this.$onEventListener(appEvents, Events.UnreadConversations, conversations => {
                addNotification(conversations.length);
            });

            this.$onEventListener(appEvents, Events.AppRefresh, () => this.refresh());
        }
    });
</script>

<style scoped>
    .notifications {
        z-index: 1100;
    }
</style>
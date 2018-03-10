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

<script>
    import TopNav from './widgets/nav/vertical/top-nav.vue';
    import BottomNav from './widgets/nav/vertical/bottom-nav.vue';
    import FlashMessages from './widgets/flash-messages.vue';
    import {cached, routeEvents, queryModalRouter, RouteEvents} from 'JS/router';
    import {mapState} from 'vuex';
    import Icon from "../../../../node_modules/vue-awesome/components/Icon.vue";
    import MainFloatingBtns from './widgets/main-floating-btns.vue';
    import Modal from './widgets/modal.vue';

    import appEvents,{ Events } from "JS/events";
    import {initialData} from 'JS/store';

    import ModalRouter from "JS/components/widgets/modal-router.vue";

    import events from 'JS/components/mixins/events';
    import Notifications from "JS/components/widgets/notifications.vue";
import { Conversation } from 'JS/api/types';

    export default {
        components: {
            Notifications,
            ModalRouter,
            Icon,
            TopNav,
            BottomNav,
            FlashMessages,
            MainFloatingBtns,
            Modal
        },
        mixins: [events],
        data: () => ({
            keepAlive: cached,
            shown: true,
            has: {},
            modals: queryModalRouter,
            loading: false,
        }),
        watch: {
            $route(to) {
                if (!to.matched) return;

                let has = {};
                for (let view of ['navigation']) {
                    has[view] = !!to.matched[0].components[view];
                }

                this.has = has;
            },
            httpConnection(val, oldVal) {
                if (oldVal !== undefined && oldVal !== null && val !== oldVal) {
                    this.notifyConnection(true, val);
                }
            },
            websocketConnection(val, oldVal) {
                if (oldVal !== undefined && oldVal !== null && val !== oldVal) {
                    this.notifyConnection(false, val);
                }
            }
        },
        computed: {
            ...mapState({
                httpConnection: state => state.connection_http,
                websocketConnection: state => state.connection_websocket
            }),
            loadingStyle() {
                return this.loading ? {visibility: 'hidden'} : {};
            }
        },
        methods: {
            /**
             * @param {boolean} isHttp
             * @param {boolean} value
             */
            notifyConnection(isHttp, value) {
                const type = isHttp ? 'http' : 'websocket';

                /** @param {boolean} value */
                const getValue = value => value ? 'true' : 'false';

                this.$store.commit('addNotification', {
                    id: `connection-${type}-${getValue(value)}`,
                    persistent: !value,
                    message: `TODO message: ${type} connection ${value ? 'regained' : 'lost'}.`,
                    type: value ? 'success' : 'danger'
                });

                this.$store.commit('removeNotification', `connection-${type}-${getValue(!value)}`);

                if (isHttp && value) {
                    this.restoreConnection();
                }
            },
            async restoreConnection() {
                if (!this.shown) return;

                this.$store.commit('httpConnection', true);

                this.shown = false;
                await this.$nextTick();
                this.shown = true;
                await this.$nextTick();
            },
        },
        mounted() {
            this.$onEventListener(routeEvents, RouteEvents.Loading, () => this.loading = true);
            this.$onEventListener(routeEvents, RouteEvents.Loaded, () => this.loading = false);

            if (initialData && initialData.unread_conversations && initialData.unread_conversations.length > 0) {
                appEvents.dispatch(Events.UnreadConversations, initialData.unread_conversations);
            }
        },
        created() {
            /**
             * @param {boolean} plural
             */
            const addConversationNotification = (plural) => {
                //TODO translate
                this.$store.commit('addNotification', {
                    id: 'chat',
                    message: plural ? 'You have new messages.' : 'You have a new message.',
                    type: 'primary'
                });
            };

            this.$onEchoGlobal('MessageSentOther', () => addConversationNotification(false));

            /**
             * @param {Conversation[]} conversations
             */
            const onUnreadConversations = conversations => {
                addConversationNotification(conversations.length > 1);
            };

            this.$onEventListener(appEvents, Events.UnreadConversations, onUnreadConversations);
        }
    };
</script>

<style scoped>
    .notifications {
        z-index: 1100;
    }
</style>
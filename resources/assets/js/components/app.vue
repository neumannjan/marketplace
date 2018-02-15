<template>
    <div class="wrapper flex-row">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <top-nav class="navbar-nav navbar-nav-fixed-top"/>
            <bottom-nav class="navbar-nav navbar-nav-fixed-bottom"/>
        </div>
        <div class="wrapper" v-if="shown">
            <main role="main"
                  :style="loadingStyle"
                  :class="['main', {'navigation-shown': has.navigation, 'navigation-not-shown': !has.navigation}]">
                <!-- NAVIGATION -->
                <div v-if="has.navigation" class="content-navigation" v-sticky>
                    <keep-alive :include="keepAlive('navigation')">
                        <router-view class="content-navigation-inner" name="navigation" v-sticky/>
                    </keep-alive>
                </div>

                <div class="main-container">
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

        <notifications class="fixed-top-right"/>
        <main-floating-btns/>
        <modal-router :data="modals"/>
    </div>
</template>

<script>
    import TopNav from './widgets/nav/vertical/top-nav';
    import BottomNav from './widgets/nav/vertical/bottom-nav';
    import FlashMessages from './widgets/flash-messages';
    import {cached, events as routeEvents, queryRouter} from 'JS/router';
    import {mapState} from 'vuex';
    import Icon from "../../../../node_modules/vue-awesome/components/Icon.vue";
    import MainFloatingBtns from './widgets/main-floating-btns';
    import Modal from './widgets/modal';

    import ModalRouter from "JS/components/widgets/modal-router";

    import events from 'JS/components/mixins/events';
    import Notifications from "JS/components/widgets/notifications";

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
            modals: queryRouter,
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
            notifyConnection(isHttp, value) {
                const type = isHttp ? 'http' : 'websocket';
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
            this.$onVue(routeEvents, 'loading', () => this.loading = true);
            this.$onVue(routeEvents, 'loaded', () => this.loading = false);
        }
    };
</script>

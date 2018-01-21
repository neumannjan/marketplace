<template>
    <div class="wrapper flex-row" v-if="shown">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <top-nav class="navbar-nav navbar-nav-fixed-top"/>
            <bottom-nav class="navbar-nav navbar-nav-fixed-bottom"/>
        </div>
        <div class="wrapper">
            <main role="main"
                  :class="['main', {'navigation-shown': has.navigation, 'navigation-not-shown': !has.navigation}]">
                <!-- NAVIGATION -->
                <div v-if="has.navigation" class="content-navigation" v-sticky>
                    <keep-alive :include="keepAlive('navigation')">
                        <router-view class="content-navigation-inner" name="navigation" v-sticky/>
                    </keep-alive>
                </div>

                <div class="main-container">
                    <!-- FLASH MESSAGES -->
                    <flash-messages class="container"/>

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

        <modal-router :data="modals"/>
    </div>
</template>

<script>
    import TopNav from './widgets/nav/vertical/top-nav';
    import BottomNav from './widgets/nav/vertical/bottom-nav';
    import FlashMessages from './widgets/flash-messages';
    import {cached, queryRouter} from 'JS/router';
    import {mapState} from 'vuex';
    import Icon from "../../../../node_modules/vue-awesome/components/Icon.vue";
    import MainFloatingBtns from './widgets/main-floating-btns';
    import Modal from './widgets/modal';
    import OfferRoute from "JS/components/routes/offer";

    import 'vue-awesome/icons/expand';
    import 'vue-awesome/icons/times';
    import ModalRouter from "JS/components/widgets/modal-router";

    export default {
        components: {
            ModalRouter,
            OfferRoute,
            Icon,
            TopNav,
            BottomNav,
            FlashMessages,
            MainFloatingBtns,
            Modal
        },
        data: () => ({
            keepAlive: cached,
            shown: true,
            has: {},
            modals: queryRouter
        }),
        computed: {
            ...mapState({
                connection: state => !state.connection_lost, //TODO show notification if connection lost
            })
        },
        methods: {
            async restoreConnection() { //TODO call when 'try for connection' pressed
                if (!this.shown) return;

                this.$store.commit('connection', true);

                this.shown = false;
                await new Promise(resolve => this.$nextTick(resolve));
                this.shown = true;
                await new Promise(resolve => this.$nextTick(resolve));
            },
        },
        mounted() {
            // set hasNavigation

            function setRouterViews(vm) {
                vm.$once('before-destroy', () => vm = undefined);

                return to => {
                    if (vm) {
                        let has = {};
                        for (let view of ['navigation']) {
                            has[view] = !!to.matched[0].components[view];
                        }

                        vm.has = has;
                    }
                }
            }

            const func = setRouterViews(this);
            func(this.$route);
            this.$router.afterEach(func);
        },
        beforeDestroy() {
            this.$emit('before-destroy');
        }
    };
</script>
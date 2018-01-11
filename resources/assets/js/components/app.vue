<template>
    <div class="wrapper flex-row" v-if="shown">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <top-nav class="navbar-nav navbar-nav-fixed-top"/>
            <bottom-nav class="navbar-nav navbar-nav-fixed-bottom"/>
        </div>
        <div class="wrapper">
            <main role="main"
                  :class="['main', {'navigation-shown': hasNavigation, 'navigation-not-shown': !hasNavigation}]">
                <!-- NAVIGATION -->
                <div v-if="hasNavigation" class="content-navigation">
                    <keep-alive :include="keepAlive('navigation')">
                        <router-view class="content-navigation-inner" name="navigation"/>
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

        <floating-btns :buttons="buttons"/>
    </div>
</template>

<script>
    import TopNav from './widgets/nav/vertical/top-nav';
    import BottomNav from './widgets/nav/vertical/bottom-nav';
    import RightNav from './widgets/nav/horizontal/right-nav';
    import FlashMessages from './widgets/flash-messages';
    import {cached} from 'JS/router';
    import {mapState} from 'vuex';
    import Icon from "../../../../node_modules/vue-awesome/components/Icon.vue";
    import FloatingBtns from './widgets/floating-btns';

    //TODO remove
    import 'vue-awesome/icons/plus';
    import 'vue-awesome/icons/comment-o';
    import 'vue-awesome/icons/bell';
    import 'vue-awesome/icons/angle-left';

    export default {
        components: {
            Icon,
            TopNav,
            BottomNav,
            RightNav,
            'flash-messages': FlashMessages,
            FloatingBtns
        },
        data: () => ({
            keepAlive: cached,
            shown: true,
            buttons: [
                {icon: 'plus', class: 'btn-primary'},
                {icon: 'comment-o'},
                {icon: 'bell'},
                {icon: 'angle-left'},
            ]
        }),
        computed: {
            ...mapState({
                connection: state => !state.connection_lost, //TODO show notification if connection lost
                hasNavigation: state => state.route_has_navigation
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
            }
        }
    };
</script>
<template>
    <div class="wrapper" v-if="shown">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <a class="navbar-brand" href="#">Nav</a> <!--TODO replace-->
            <top-nav class="navbar-nav"/>
            <bottom-nav class="navbar-nav navbar-nav-fixed-bottom bg-dark"/>
        </div>
        <div class="main-content">
            <main role="main" class="main container">
                <flash-messages/>
                <keep-alive :include="keepAlive">
                    <router-view/>
                </keep-alive>
            </main>

            <footer class="footer">
                <div class="footer-inner">
                    <span class="ml-auto">&copy; Company 2017</span> <!--TODO replace-->
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import TopNavComponent from './widgets/nav/top-nav.vue';
    import BottomNavComponent from './widgets/nav/bottom-nav.vue';
    import FlashMessagesComponent from './widgets/flash-messages.vue';
    import {cached} from 'JS/router';
    import {mapState} from 'vuex';

    export default {
        components: {
            'top-nav': TopNavComponent,
            'bottom-nav': BottomNavComponent,
            'flash-messages': FlashMessagesComponent,
        },
        data: () => ({
            keepAlive: cached,
            shown: true
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
            }
        }
    };
</script>
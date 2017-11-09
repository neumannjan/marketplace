<template>
    <div class="wrapper">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <a class="navbar-brand" href="#">Nav</a>
            <top-nav class="navbar-nav"></top-nav>
            <bottom-nav class="navbar-nav mt-auto"></bottom-nav>
        </div>
        <div class="main-content">
            <main role="main" class="main container">
                <flash-messages></flash-messages>
                <router-view></router-view>
            </main>

            <footer class="footer">
                <div class="footer-inner">
                    <span class="ml-auto">&copy; Company 2017</span>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import TopNavComponent from './widgets/nav/top-nav.vue';
    import BottomNavComponent from './widgets/nav/bottom-nav.vue';
    import FlashMessagesComponent from './widgets/flash-messages.vue';

    export default {
        components: {
            'top-nav': TopNavComponent,
            'bottom-nav': BottomNavComponent,
            'flash-messages': FlashMessagesComponent,
        }
    };
</script>
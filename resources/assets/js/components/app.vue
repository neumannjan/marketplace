<template>
    <div class="wrapper">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <a class="navbar-brand" href="#">Nav</a>
            <side-nav class="navbar-nav" :items="[
                {
                    label: 'Dashboard',
                    icon: 'fa fa-home',
                    route: 'index'
                },
                {
                    label: 'Admin',
                    icon: 'fa fa-cog',
                    route: 'user',
                    params: {
                        name: 'admin'
                    }
                }
            ]"></side-nav>
            <user-nav class="navbar-nav mt-auto"></user-nav>
        </div>
        <div class="main-content">
            <main role="main" class="main container">
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
    import NavComponent from './widgets/nav.vue';
    import UserNavComponent from './widgets/user-nav.vue';

    export default {
        components: {
            'side-nav': NavComponent,
            'user-nav': UserNavComponent,
        }
    };
</script>
<template>
    <div class="wrapper">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <a class="navbar-brand" href="#">Nav</a>
            <side-nav class="navbar-nav" :items="[
                {
                    name: 'index',
                    label: 'Dashboard',
                    icon: 'fa fa-home'
                },
                {
                    name: 'user',
                    label: 'Admin',
                    params: {
                        name: 'admin'
                    },
                    icon: 'fa fa-cog'
                }
            ]"></side-nav>
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

    export default {
        components: {
            'side-nav': NavComponent
        }
    };
</script>
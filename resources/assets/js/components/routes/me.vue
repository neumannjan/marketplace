<template>
    <user-route :username="user.username"/>
</template>

<script>
    import UserRoute from "JS/components/routes/user";
    import AuthGuard from "JS/router/guards/auth";
    import route from "JS/components/mixins/route";
    import {mapState} from "vuex";

    export default {
        name: 'me-route',
        mixins: [AuthGuard, route],
        components: {
            UserRoute
        },
        computed: {
            title() {
                return this.user.display_name;
            },
            ...mapState({
                user: state => state.user
            })
        },
    };
</script>
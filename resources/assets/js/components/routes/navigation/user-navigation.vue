<template>
    <div>
        <template v-if="user">
            <h1>{{ user.display_name }}</h1>
        </template>
    </div>
</template>

<script>
    import {events as routeEvents} from 'JS/router';

    export default {
        name: 'user-route-navigation',
        data: () => ({
            user: null
        }),
        created() {
            routeEvents.$once('has-user', user => {
                this.user = user;
            });
        }
    };
</script>
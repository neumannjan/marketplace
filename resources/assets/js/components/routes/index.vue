<template>
    <div>
        <h1>Index page</h1>
        <button class="btn btn-primary" @click="toggleAuth">Log {{ auth ? "out" : "in" }}</button>
    </div>
</template>

<script>
    import title from './../mixins/title';
    import {mapState} from 'vuex';

    export default {
        mixins: [title],
        data: () => ({
            title: "Index page"
        }),
        computed: {
            ...mapState({
                auth: state => state.is_authenticated
            })
        },
        methods: {
            toggleAuth() {
                this.$store.commit('auth', !this.auth);
            }
        }
    };
</script>
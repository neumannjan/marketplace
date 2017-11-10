<template>
    <div>
        <h1>Index page</h1>

        <button class="btn btn-primary" @click="flash">Gimme dat flash</button>
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
            flash() {
                this.$store.commit('addFlash', {
                    type: 'success',
                    key: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
                    message: 'Test flash!!'
                });
            }
        }
    };
</script>
<template>
    <div>
        <h1>Index page</h1>
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
        }
    };
</script>
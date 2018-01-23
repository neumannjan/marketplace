<template>
    <form @submit.prevent="submit">
        <!-- TODO translate -->
        <div class="input-group input-group-lg">
            <div class="input-group-prepend">
                <label for="search" class="search-icon input-group-text">
                    <icon name="search" label="Search"/>
                </label>
            </div>
            <input name="search"
                   id="search"
                   class="search-form form-control"
                   type="text"
                   placeholder="Search"
                   v-model="input">
        </div>
    </form>
</template>

<script>
    import "vue-awesome/icons/search";

    export default {
        name: "search",
        data: () => ({
            input: '',
        }),
        methods: {
            submit() {
                this.$emit('search', this.input);
            }
        }
    }
</script>

<style scoped>
    .search-icon {
        background: #fff;
        border-right: none;
    }

    .search-form {
        border-left: none;
    }
</style>
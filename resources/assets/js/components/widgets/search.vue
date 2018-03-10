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
                   ref="input"
                   :value="value"
                   @input="e => input(e.target.value)">
        </div>
    </form>
</template>

<script>
    import "vue-awesome/icons/search";

    export default {
        name: "search",
        props: {
            value: String,
        },
        methods: {
            /**
             * @param {string} value
             */
            input(value) {
                this.$emit('input', value);
            },
            submit() {
                //@ts-ignore
                this.$emit('submit', this.$refs.input.value);
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
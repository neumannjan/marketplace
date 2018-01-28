<template>
    <span v-show="error" class="invalid-feedback"><strong v-if="error">{{ error }}</strong></span>
</template>

<script>
    import {helpers as storeHelpers} from 'JS/store';

    export default {
        name: 'validation-message',
        props: {
            validation: Object,
            serverValidation: Array,
            label: {
                type: String,
                required: true,
            },
            input: {}
        },
        data: () => ({
            serverDirty: false,
        }),
        watch: {
            serverValidation() {
                this.serverDirty = (this.serverValidation && this.serverValidation.length > 0);
            },
            valid(val) {
                this.$emit('valid', val);
            },
            error(val) {
                this.$emit('error', val);
            },
            input() {
                if (this.validation)
                    this.validation.$touch();
                this.serverDirty = false;
            }
        },
        computed: {
            valid() {
                return (this.serverDirty && (!this.serverValidation || this.serverValidation.length === 0)) || (this.validation && this.validation.$dirty && !this.validation.$invalid);
            },
            error() {
                if (this.serverDirty && this.serverValidation && this.serverValidation.length > 0) {
                    return this.serverValidation[0];
                }

                if (!this.validation || !this.validation.$dirty || this.valid) {
                    return null;
                }

                let params = Object.keys(this.validation)
                    .filter(key => key[0] !== '$');

                for (let key of params) {
                    if (!this.validation[key]) {
                        const singleParams = this.validation.$params[key];
                        const message = (singleParams && singleParams.message) ? singleParams.message : key;

                        if (singleParams)
                            delete singleParams.message;

                        return storeHelpers.trans('validation.' + message, {
                            attribute: this.label,
                            ...singleParams
                        });
                    }
                }

                return null;
            }
        },
    };
</script>

<style>
    .invalid-feedback {
        display: block;
    }
</style>
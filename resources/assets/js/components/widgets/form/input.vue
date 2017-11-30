<template>
    <div>
        <label :for="id">
            <slot>{{ label }}</slot>
        </label>
        <input :id="id" :name="name" :type="type" :class="['form-control', {'is-invalid': error, 'is-valid': valid}]"
               :value="value"
               @input="onInput" @blur="touch()" :autofocus="autofocus"
               :required="required" :aria-describedby="hint ? hintId : false">
        <span v-if="error" class="invalid-feedback"><strong>{{ error }}</strong></span>
        <small v-if="!!hint" :id="hintId" class="form-text text-muted">{{ hint }}</small>
    </div>
</template>

<script>
    import {helpers as storeHelpers} from '../../../store/store';

    export default {
        data: () => ({
            id: null,
            serverDirty: false,
        }),
        props: {
            validation: Object,
            serverValidation: Array,
            label: {
                type: String,
                required: true
            },
            hint: String,
            type: {
                type: String,
                default: 'text'
            },
            name: {
                type: String,
                required: true
            },
            value: String,
            autofocus: Boolean,
            required: Boolean,
        },
        watch: {
            serverValidation() {
                this.serverDirty = (this.serverValidation && this.serverValidation.length > 0);
            }
        },
        methods: {
            touch() {
                if (this.validation)
                    this.validation.$touch();
            },
            onInput(event) {
                this.$emit('input', event.target.value);
                this.touch();
                this.serverDirty = false;
            }
        },
        computed: {
            hintId() {
                return this.id + '-hint';
            },
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
                        return storeHelpers.trans('validation.' + key, {
                            attribute: this.label,
                            ...this.validation.$params[key]
                        });
                    }
                }

                return null;
            }
        },
        mounted() {
            this.id = 'input-' + this._uid;
        }
    };
</script>
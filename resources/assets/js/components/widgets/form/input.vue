<template>
    <div>
        <label :for="id">
            <slot>{{ label }}</slot>
        </label>
        <input :id="id" :type="type" :class="['form-control', {'is-invalid': error, 'is-valid': valid}]" :value="value"
               @input="onInput" @blur="touch()" :autofocus="autofocus"
               :required="required" :aria-describedby="hint ? hintId : false">
        <span v-if="error" class="invalid-feedback"><strong>{{ error }}</strong></span>
        <small v-if="!!hint" :id="hintId" class="form-text text-muted">{{ hint }}</small>
    </div>
</template>

<script>
    import {helpers} from '../../../store/store';

    export default {
        data: () => ({
            id: null,
            serverDirty: false,
        }),
        props: {
            validation: Object,
            serverValidation: Array,
            label: String,
            hint: String,
            type: {
                type: String,
                default: 'text'
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
                this.validation.$touch();
                this.serverDirty = false;
            },
            onInput(event) {
                this.$emit('input', event.target.value);
                this.touch();
            }
        },
        computed: {
            hintId() {
                return this.id + '-hint';
            },
            valid() {
                return (this.serverDirty && (!this.serverValidation || this.serverValidation.length === 0)) || (this.validation.$dirty && !this.validation.$invalid);
            },
            error() {
                if (this.serverDirty && this.serverValidation && this.serverValidation.length > 0) {
                    return this.serverValidation[0];
                }

                if (!this.validation.$dirty || this.valid) {
                    return null;
                }

                let params = Object.keys(this.validation)
                    .filter(key => key[0] !== '$');

                for (let key of params) {
                    if (!this.validation[key]) {
                        return helpers.trans('validation.' + key, {
                            attribute: this.label,
                            ...this.validation.$params[key]
                        });
                    }
                }

                return null;
            }
        },
        mounted() {
            this.id = this._uid;
        }
    };
</script>
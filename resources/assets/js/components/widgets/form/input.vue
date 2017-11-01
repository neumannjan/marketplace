<template>
    <div>
        <label :for="id">
            <slot></slot>
        </label>
        <input :id="id" :type="type" :class="['form-control', {'is-invalid': error, 'is-valid': valid}]" :value="value"
               @input="$emit('input', $event.target.value)" @blur="validation.$touch()" :autofocus="autofocus"
               :required="required" :aria-describedby="hint ? hintId : false">
        <span v-if="error" class="invalid-feedback"><strong>{{ error }}</strong></span>
        <small v-if="!!hint" :id="hintId" class="form-text text-muted">{{ hint }}</small>
    </div>
</template>

<script>
    export default {
        data: () => ({
            id: null,
        }),
        props: {
            validation: Object,
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
        computed: {
            hintId() {
                return this.id + '-hint';
            },
            dirty() {
                return this.validation.$dirty;
            },
            valid() {
                return this.dirty && !this.validation.$invalid;
            },
            error() {
                if (!this.dirty || this.valid) {
                    return null;
                }

                let params = Object.keys(this.validation)
                    .filter(key => key[0] !== '$');

                for (let key of params) {
                    if (!this.validation[key])
                        return key;
                }

                return null;
            }
        },
        mounted() {
            this.id = this._uid;
        }
    };
</script>
<template>
    <div>
        <label v-if="showLabel" :for="id">
            <slot>{{ label }}</slot>
        </label>
        <textarea v-if="textarea"
                  :rows="textarea"
                  :id="id" :name="name" :class="['form-control', {'is-invalid': error, 'is-valid': valid}]"
                  :placeholder="placeholder"
                  :title="label"
                  @input="onInput" @blur="touch()" :autofocus="autofocus"
                  :required="required" :aria-describedby="hint ? hintId : false">{{ value }}</textarea>
        <input v-else
               :id="id" :name="name" :type="type" :class="['form-control', {'is-invalid': error, 'is-valid': valid}]"
               :value="value"
               :placeholder="placeholder"
               :title="label"
               @input="onInput" @blur="touch()" :autofocus="autofocus"
               :required="required" :aria-describedby="hint ? hintId : false">
        <validation-message :validation="validation" :server-validation="serverValidation"
                            :label="errorLabel ? errorLabel : label" :input="input"
                            @valid="v => valid = v"
                            @error="e => error = e"/>
        <small v-if="!!hint" :id="hintId" class="form-text text-muted">{{ hint }}</small>
    </div>
</template>

<script>
    import ValidationMessage from "JS/components/widgets/form/validation-message";

    export default {
        components: {ValidationMessage},
        data: () => ({
            id: null,
            valid: false,
            error: null,
            input: '',
        }),
        props: {
            validation: Object,
            textarea: Number,
            serverValidation: Array,
            label: {
                type: String,
                required: true
            },
            showLabel: {
                type: Boolean,
                default: true
            },
            placeholder: String,
            hint: String,
            type: {
                type: String,
                default: 'text'
            },
            name: {
                type: String,
                required: true
            },
            errorLabel: String,
            value: String,
            autofocus: Boolean,
            required: Boolean,
        },
        methods: {
            touch() {
                if (this.validation)
                    this.validation.$touch();
            },
            onInput(event) {
                this.input = event.target.value;
                this.$emit('input', event.target.value);
                this.touch();
            },
        },
        computed: {
            hintId() {
                return this.id + '-hint';
            },
        },
        mounted() {
            this.id = 'input-' + this._uid;
        }
    };
</script>
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
                  @input="onInput" @blur="touch()" v-focus="autofocus"
                  :value="value"
                  :maxlength="maxlength"
                  :aria-describedby="hint ? hintId : false"></textarea>
        <input v-else
               :id="id" :name="name" :type="type" :class="['form-control', {'is-invalid': error, 'is-valid': valid}]"
               :value="value"
               :placeholder="placeholder"
               :maxlength="maxlength"
               :title="label"
               @input="onInput" @blur="touch()" v-focus="autofocus"
               :aria-describedby="hint ? hintId : false">
        <validation-message :validation="validation" :server-validation="serverValidation"
                            :label="errorLabel ? errorLabel : label" :input="input"
                            @valid="v => valid = v"
                            @error="e => error = e"/>
        <small v-if="!!hint" :id="hintId" class="form-text text-muted">{{ hint }}</small>
    </div>
</template>

<script>
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";

    let nextID = 0;

    export default {
        name: 'form-input',
        components: {ValidationMessage},
        data: () => ({
            /** @type {string | null} */
            id: null,
            /** @type {boolean} */
            valid: false,
            /** @type {boolean | null} */
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
            maxlength: Number
        },
        methods: {
            touch() {
                if (this.validation)
                    this.validation.$touch();
            },
            /**@param {Event} event */
            onInput(event) {
                //@ts-ignore
                this.input = event.target.value;
                //@ts-ignore
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
            this.id = 'input-' + nextID++;
        }
    };
</script>
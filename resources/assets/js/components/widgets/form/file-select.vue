<template>
    <div class="form-group">
        <label v-if="label" :for="id">
            <slot>{{ label }}</slot>
        </label>
        <div class="custom-file">
            <input type="file" :name="name" :multiple="multiple" @change="onFileChange"
                   :class="['custom-file-input', {'is-invalid': error, 'is-valid': valid}]"
                   :id="id" ref="file">
            <!-- TODO -->
            <label class="custom-file-label" :data-button="'Browse'" :for="id"><span>{{ fileInfo }}</span></label>
        </div>
        <validation-message :validation="validation" :server-validation="serverValidation"
                            :label="errorLabel ? errorLabel : label" :input="files"
                            @valid="v => valid = v"
                            @error="e => error = e"/>
    </div>
</template>

<script>
    import ValidationMessage from "JS/components/widgets/form/validation-message";

    export default {
        components: {ValidationMessage},
        name: "file-select",
        data: () => ({
            id: null,
            files: null,
            valid: false,
            error: null,
        }),
        props: {
            name: String,
            label: {
                type: String,
                default: null
            },
            multiple: Boolean,
            validation: Object,
            serverValidation: Array,
            errorLabel: String,
        },
        computed: {
            fileInfo() {
                //TODO

                const amount = this.files ? this.files.length : 0;

                let amountStr;

                switch (amount) {
                    case 0:
                        if (this.multiple)
                            return "Choose files";
                        else
                            return "Choose file";
                    case 1:
                        amountStr = '1 file';
                        break;
                    default:
                        amountStr = `${this.files.length} files`;
                        break;
                }

                return `${amountStr}: ${Array.from(this.files).map(file => file.name).join(' | ')}`;
            }
        },
        methods: {
            onFileChange(e) {
                this.files = e.target.files || e.dataTransfer.files;
                this.$emit('input', this.files);
            }
        },
        mounted() {
            this.id = 'input-' + this._uid;
        }
    }
</script>

<style lang="scss" type="text/scss">
    @import "~CSS/includes";

    .custom-file-label {
        display: flex;
        flex-direction: row;

        span {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            padding-right: 20px;
        }

        &:after {
            content: attr(data-button);
            position: relative;
            margin: #{-$input-padding-y} #{-$input-padding-x} 0 auto;
        }
    }
</style>
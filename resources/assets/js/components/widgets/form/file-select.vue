<template>
    <div class="form-group">
        <label v-if="label" :for="id">
            <slot>{{ label }}</slot>
        </label>
        <div class="custom-file">
            <input type="file" :name="name" :multiple="multiple" @change="onFileChange"
                   :accept="accept"
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
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";
    import Vue from "vue";

    export default Vue.extend({
        components: {ValidationMessage},
        name: "file-select",
        data: () => ({
            /** @type {string | null} */
            id: null,
            /** @type {Array | null} */
            files: null,
            /** @type {boolean} */
            valid: false,
            /** @type {null | boolean} */
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
            accept: String
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
                        //@ts-ignore
                        amountStr = `${this.files.length} files`;
                        break;
                }

                return `${amountStr}: ${Array.from(this.files).map(file => file.name).join(' | ')}`;
            }
        },
        methods: {
            /** @param {any} e */
            async onFileChange(e) {
                this.files = null;
                await this.$nextTick();
                this.files = e.target.files || e.dataTransfer.files;
                this.$emit('input', this.files);
            }
        },
        mounted() {
            this.id = 'input-' + this._uid;
        }
    });
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
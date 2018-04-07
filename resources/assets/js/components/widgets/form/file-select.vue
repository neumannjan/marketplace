<template>
    <div class="form-group">
        <label v-if="label || $slots.default" :for="id">
            <slot>{{ label }}</slot>
        </label>
        <div class="input-group">
            <div :class="['custom-file', $slots.append ? 'custom-file-append' : '']">
                <input type="file" :name="name" :multiple="multiple" @change="onFileChange"
                       v-if="!reseting"
                       :accept="accept"
                       :class="['custom-file-input', {'is-invalid': error, 'is-valid': valid}]"
                       :id="id" ref="file">
                <label class="custom-file-label" :data-button="translations.browse"
                       :for="id"><span>{{ fileInfo }}</span></label>
            </div>
            <div v-if="$slots.append" class="input-group-append">
                <slot name="append"/>
            </div>
        </div>
        <validation-message :validation="validation" :server-validation="serverValidation"
                            :label="errorLabel ? errorLabel : label" :input="files"
                            @valid="v => valid = v"
                            @error="e => error = e"/>
        <small v-if="!!hint" :id="hintId" class="form-text text-muted">{{ hint }}</small>
    </div>
</template>

<script lang="ts">
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";
    import {Component, Prop, Vue, Watch} from "JS/components/class-component";
    import {Vuelidate} from "vuelidate";
    import {TranslationMessages} from "lang.js";

    let nextID = 0;

    @Component({
        name: "file-select",
        components: {
            ValidationMessage
        },
    })
    export default class FileSelect extends Vue {
        id: string | null = null;
        files: FileList | null = null;
        valid: boolean = false;
        error: null | boolean = null;
        reseting: boolean = false;

        @Prop({type: String})
        name: string | undefined;

        @Prop({type: String, default: null})
        label!: string | null;

        @Prop({type: String})
        hint: string | undefined;

        @Prop({type: Boolean})
        multiple: boolean | undefined;

        @Prop({type: Object})
        validation: Vuelidate | undefined;

        @Prop({type: Array})
        serverValidation: Array<string> | undefined;

        @Prop({type: String})
        errorLabel: string | undefined;

        @Prop({type: String})
        accept: string | undefined;

        @Prop({})
        value: FileList | undefined;

        get fileInfo() {
            let amountStr = '';

            const amount = this.files ? this.files.length : 0;

            switch (amount) {
                case 0:
                    if (this.multiple)
                        return this.$store.getters.trans('interface.form.file-select-multiple');
                    else
                        return this.$store.getters.trans('interface.form.file-select');
                default:
                    break;
            }

            if (this.multiple) {
                amountStr = this.$store.getters.transChoice('interface.form.file-select-listed', amount, {
                    amount: amount
                }) + ": ";
            }

            return amountStr + Array.from(this.files!).map(file => file.name).join(' | ');
        }

        get translations(): TranslationMessages {
            return {
                browse: this.$store.getters.trans('interface.button.browse'),
            }
        }

        async onFileChange(e: Event) {
            this.files = null;
            await this.$nextTick();
            this.files = (e.target as HTMLInputElement).files || (e as DragEvent).dataTransfer.files;
            this.$emit('input', this.files);
        }

        get hintId() {
            return this.id + '-hint';
        }

        @Watch('value')
        async onValueChanged(value: FileList | undefined) {
            if (value === undefined) {
                this.files = null;
                this.reseting = true;
                await this.$nextTick();
                this.reseting = false;
            }
        }

        mounted() {
            this.id = 'input-' + nextID++;
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
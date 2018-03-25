<template>
    <div class="form-group">
        <label v-if="label || $slots.default" :for="id">
            <slot>{{ label }}</slot>
        </label>
        <div class="input-group">
            <div :class="['custom-file', $slots.append ? 'custom-file-append' : '']">
                <input type="file" :name="name" :multiple="multiple" @change="onFileChange"
                       :accept="accept"
                       :class="['custom-file-input', {'is-invalid': error, 'is-valid': valid}]"
                       :id="id" ref="file">
                <!-- TODO -->
                <label class="custom-file-label" :data-button="'Browse'" :for="id"><span>{{ fileInfo }}</span></label>
            </div>
            <div v-if="$slots.append" class="input-group-append">
                <slot name="append"/>
            </div>
        </div>
        <validation-message :validation="validation" :server-validation="serverValidation"
                            :label="errorLabel ? errorLabel : label" :input="files"
                            @valid="v => valid = v"
                            @error="e => error = e"/>
    </div>
</template>

<script lang="ts">
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";
    import {Component, Prop, Vue} from "JS/components/class-component";
    import {Vuelidate} from "vuelidate";

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

        @Prop({type: String})
        name: string | undefined;

        @Prop({type: String, default: null})
        label!: string | null;

        @Prop({type: Boolean})
        multiple: boolean | undefined;

        @Prop({type: Object})
        validation: Vuelidate | undefined;

        @Prop({type: Array})
        serverValidation: Array<string> | undefined;

        @Prop({type: String})
        errorLabel: string | undefined

        @Prop({type: String})
        accept: string | undefined

        get fileInfo() {
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
                    amountStr = `${amount} files`;
                    break;
            }

            return `${amountStr}: ${Array.from(this.files!).map(file => file.name).join(' | ')}`;
        }

        async onFileChange(e: Event) {
            this.files = null;
            await this.$nextTick();
            this.files = (e.target as HTMLInputElement).files || (e as DragEvent).dataTransfer.files;
            this.$emit('input', this.files);
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
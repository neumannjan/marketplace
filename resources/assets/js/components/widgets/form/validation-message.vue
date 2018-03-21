<template>
    <span v-show="error" class="invalid-feedback"><strong v-if="error">{{ error }}</strong></span>
</template>

<script lang="ts">
    import {helpers as storeHelpers} from 'JS/store';
    import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
    import { Vuelidate } from 'vuelidate';

    @Component({
        name: 'validation-message'
    })
    export default class ValidationMessage extends Vue {
        @Prop(Object)
        validation: Vuelidate | undefined;

        @Prop(Array)
        serverValidation: Array<string> | undefined;

        @Prop({type: String, required: true})
        label!: string;

        @Prop()
        input: any;

        serverDirty: boolean = false;

        @Watch('serverValidation')
        onServerValidationChanged() {
            this.serverDirty = (!!this.serverValidation && this.serverValidation.length > 0);
        };

        @Watch('valid')
        onValidChanged(val: boolean) {
            this.$emit('valid', val);
        };
        
        @Watch('error')
        onErrorChanged(val: string | null) {
            this.$emit('error', val);
        };
        
        @Watch('input')
        onInputChanged() {
            if (this.validation !== undefined) {
                this.validation.$touch();
            }

            this.serverDirty = false;
        }

        get valid(): boolean {
            return (this.serverDirty && (!this.serverValidation || this.serverValidation.length === 0)) || (!!this.validation && this.validation.$dirty && !this.validation.$invalid);
        }

        get error(): string | null {
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
    }
</script>

<style>
    .invalid-feedback {
        display: block;
    }
</style>
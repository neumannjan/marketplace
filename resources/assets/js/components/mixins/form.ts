import Api from 'JS/api';
import helpers from 'JS/lib/helpers';
import debounce from 'lodash/debounce';
import Vue from "vue";
import Component from 'vue-class-component';

interface FormMixinData {
    validation: {
        [index: string]: object
    }
}

interface Function {
    (...params: any[]): void
}

@Component({})
class FormMixin extends Vue implements FormMixinData {
    validation: { [index: string]: object; } = {};

    $serverValidationOn(key: string): object {
        let first = key.split('.')[0];
        if (!<object>this.validation[first])
            this.$set(this.validation, first, {});

        return helpers.safeGet(this.validation, key);
    }

    $submitForm(requestName: string, selectorKey: string, onSuccess: Function, data: FormData | false = false,
                bypassValidation: boolean = false) {
        doSubmitForm(this, requestName, selectorKey, onSuccess, data, bypassValidation);
    }
}

type VM = { [index: string]: any } & Vue & FormMixinData;

const doSubmitForm = debounce((vm: VM, requestName: string, selectorKey: string, onSuccess: Function,
                             formData: FormData | false, bypassValidation: boolean) => {
    vm.$v.$reset();
    if (bypassValidation || !vm.$v.$invalid) {
        vm.$set(vm.validation, selectorKey, {});

        let data;

        if (formData) {
            if (!(formData instanceof FormData))
                data = new FormData(formData);
            else
                data = formData;
        } else {
            data = vm[selectorKey];
        }

        Api.requestSingle(requestName, data)
            .then(onSuccess)
            .catch((result) => {
                if (result.api && result.api.validation) {
                    vm.$set(vm.validation, selectorKey, result.api.validation);
                }
            })
            .then(() => vm.$v.$touch());
    } else {
        vm.$v.$touch();
    }
}, 1000, {leading: true, trailing: false});

export default FormMixin;
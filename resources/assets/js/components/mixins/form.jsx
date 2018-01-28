import Api from 'JS/api';
import helpers from 'JS/helpers';
import debounce from 'lodash/debounce';

let doSubmitForm = debounce((vm, requestName, selectorKey, onSuccess, formData, bypassValidation) => {
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
            .finally(() => vm.$v.$touch());
    } else {
        vm.$v.$touch();
    }
}, 1000, {leading: true, trailing: false});

export default {
    data: () => ({
        validation: {}
    }),
    methods: {
        $serverValidationOn(key) {
            let first = key.split('.')[0];
            if (!this.validation[first])
                this.$set(this.validation, first, {});

            return helpers.safeGet(this.validation, key);
        },
        /**
         * @param {string} requestName
         * @param {string} selectorKey
         * @param {function} onSuccess
         * @param {HTMLFormElement|FormData} data
         * @param {boolean} bypassValidation
         */
        $submitForm(requestName, selectorKey, onSuccess, data = false, bypassValidation = false) {
            doSubmitForm(this, requestName, selectorKey, onSuccess, data, bypassValidation);
        }
    },
}
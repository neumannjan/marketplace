import Api from 'JS/api';
import helpers from 'JS/helpers';
import debounce from 'lodash/debounce';

let doSubmitForm = debounce((vm, requestName, selectorKey, onSuccess) => {
    vm.$v.$reset();
    if (!vm.$v.$invalid) {
        vm.$set(vm.validation, selectorKey, {});
        Api.requestSingle(requestName, vm[selectorKey])
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
        $submitForm(requestName, selectorKey, onSuccess) {
            doSubmitForm(this, requestName, selectorKey, onSuccess);
        }
    },
}
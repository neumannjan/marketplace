import Api from '../../api';
import helpers from '../../helpers';

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
            //TODO ensure that you cannot just hold the enter key and call the same request a sh*t ton of times.
            this.$v.$reset();
            if (!this.$v.$invalid) {
                this.$set(this.validation, selectorKey, {});
                Api.SingleRequest(requestName, this[selectorKey])
                    .then(() => this.$v.$touch())
                    .success(onSuccess)
                    .error((result) => {
                        if (result.validation) {
                            this.$set(this.validation, selectorKey, result.validation);
                        }
                    })
                    .fire();
            } else {
                this.$v.$touch();
            }
        },
    }
}
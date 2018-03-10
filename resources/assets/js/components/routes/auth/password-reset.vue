<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>{{ title }}</h1>

        <form id="form-register">
            <form-input class="form-group"
                        label="E-mail"
                        name="email"
                        :serverValidation="$serverValidationOn('form.email')"
                        :validation="$v.form.email"
                        v-model="form.email"
                        type="email" required autofocus=""/>
            <form-input class="form-group"
                        label="Password"
                        name="password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password" required/>
            <form-input class="form-group"
                        label="Confirm Password"
                        name="password_confirmation"
                        :serverValidation="$serverValidationOn('form.password_confirmation')"
                        :validation="$v.form.password_confirmation"
                        v-model="form.password_confirmation"
                        type="password" required/>

            <div class="form-group">
                <button type="submit" id="submit" class="btn btn-primary" @click.prevent="submit">Reset Password
                </button>
                <!-- TODO translate -->
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {email, minLength, required, sameAs} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import form from 'JS/components/mixins/form';

    export default {
        mixins: [route, form],
        props: {
            token: String
        },
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            form: {
                email: "",
                password: "",
                password_confirmation: "",
            }
        }),
        methods: {
            submit() {
                this.$submitForm('password-reset', 'form', () => this.$router.push({name: 'index'}));
            },
        },
        mounted() {
            this.form.token = this.token;
        },
        computed: {
            title() {
                return 'Reset Password';
            }
        },
        validations: {
            form: {
                email: {
                    required,
                    email,
                },
                password: {
                    required,
                    min: minLength(8),

                    /**
                     * @param {string} value
                     */
                    containsNonNumeric(value) {
                        if (value === '') return true;

                        return value.match(/[^0-9]/) !== null;
                    },

                    /** 
                     * @param {string} value
                     */
                    containsNumeric(value) {
                        if (value === '') return true;

                        return value.match(/[0-9]/) !== null;
                    }
                },
                password_confirmation: {
                    required,
                    confirmed: sameAs('password'),
                },
            }
        }
    };
</script>

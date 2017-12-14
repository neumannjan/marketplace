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
                        type="email" required autofocus=""></form-input>
            <form-input class="form-group"
                        label="Password"
                        name="password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password" required></form-input>
            <form-input class="form-group"
                        label="Confirm Password"
                        name="password_confirmation"
                        :serverValidation="$serverValidationOn('form.password_confirmation')"
                        :validation="$v.form.password_confirmation"
                        v-model="form.password_confirmation"
                        type="password" required></form-input>

            <div class="form-group">
                <button type="submit" id="submit" class="btn btn-primary" @click.prevent="submit">Reset Password
                </button>
                <!-- TODO translate -->
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from '../../widgets/form/input.vue';
    import SelectComponent from '../../widgets/form/select.vue';

    import {required, minLength, email, sameAs} from 'vuelidate/lib/validators';

    import route from '../../mixins/route';
    import form from './../../mixins/form';

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
                    nonNumeric(value) {
                        if (value === '') return true;

                        return value.match(/[^0-9]/) !== null;
                    },
                    numeric(value) {
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

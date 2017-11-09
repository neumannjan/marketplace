<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>Register</h1>

        <form id="form-register">
            <form-input class="form-group"
                        label="Username"
                        :serverValidation="$serverValidationOn('form.username')"
                        :validation="$v.form.username"
                        v-model="form.username"
                        required autofocus></form-input>
            <form-input class="form-group"
                        label="E-mail"
                        :serverValidation="$serverValidationOn('form.email')"
                        :validation="$v.form.email"
                        v-model="form.email"
                        type="email" required></form-input>
            <form-input class="form-group"
                        label="Display Name"
                        :serverValidation="$serverValidationOn('form.display_name')"
                        :validation="$v.form.display_name"
                        v-model="form.display_name"
                        required></form-input>
            <form-input class="form-group"
                        label="Password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password" required></form-input>
            <form-input class="form-group"
                        label="Password"
                        :serverValidation="$serverValidationOn('form.password_confirmation')"
                        :validation="$v.form.password_confirmation"
                        v-model="form.password_confirmation"
                        type="password" required></form-input>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" @click.prevent="submit">Register</button>
                <!-- TODO translate -->
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from '../widgets/form/input.vue';
    import SelectComponent from '../widgets/form/select.vue';

    import {required, minLength, email, sameAs} from 'vuelidate/lib/validators';

    import title from './../mixins/title';
    import form from './../mixins/form';

    export default {
        mixins: [title, form],
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            form: {
                username: "",
                email: "",
                display_name: "",
                password: "",
                password_confirmation: "",
            }
        }),
        methods: {
            submit() {
                this.$submitForm('register', 'form', () => this.$router.push({name: 'index'}));
            },
        },
        computed: {
            title() {
                return 'Register';
            }
        },
        validations: {
            form: {
                username: {
                    required,
                    min: minLength(5),
                    slug(value) {
                        if(value === '') return true;

                        return (value.match(/^[a-zA-Z0-9-_]+$/) !== null);
                    }
                },
                email: {
                    required,
                    email,
                },
                display_name: {

                },
                password: {
                    required,
                    min: minLength(8),
                },
                password_confirmation: {
                    required,
                    confirmed: sameAs('password'),
                },
            }
        }
    };
</script>
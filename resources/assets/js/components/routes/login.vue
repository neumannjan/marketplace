<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>Login</h1>

        <form id="form-login">
            <form-input class="form-group"
                        label="Login"
                        :serverValidation="$serverValidationOn('form.login')"
                        :validation="$v.form.login"
                        v-model="form.login"
                        hint="Username or email"
                        required autofocus></form-input>
            <form-input class="form-group"
                        label="Password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password" required></form-input>

            <form-select class="form-group" v-model="form.remember">Remember Me</form-select>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" @click.prevent="submit">Login</button>
                <!-- TODO translate -->
                <router-link class="btn btn-link" :to="{ name: 'password-request' }">Forgot Your Password?</router-link>
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from '../widgets/form/input.vue';
    import SelectComponent from '../widgets/form/select.vue';

    import {required, minLength} from 'vuelidate/lib/validators';

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
                login: "",
                password: "",
                remember: false,
            }
        }),
        methods: {
            submit() {
                this.$submitForm('login', 'form', () => this.$router.push({name: 'index'}));
            },
        },
        computed: {
            title() {
                return 'Login';
            }
        },
        validations: {
            form: {
                login: {
                    required,
                    minLength: minLength(5),
                },
                password: {
                    required,
                    minLength: minLength(8),
                },
            }
        }
    };
</script>
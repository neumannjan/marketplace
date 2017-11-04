<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>Login</h1>

        <form id="form-login">
            <form-input class="form-group"
                        label="Login"
                        :serverValidation="validation && validation.login ? validation.login : null"
                        :validation="$v.form.login"
                        v-model="form.login"
                        required autofocus></form-input>
            <form-input class="form-group"
                        label="Password"
                        :serverValidation="validation && validation.password ? validation.password : null"
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
    import Api from '../../api';

    import {required, minLength} from 'vuelidate/lib/validators';

    import title from './../mixins/title';

    export default {
        mixins: [title],
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            form: {
                login: "",
                password: "",
                remember: false,
            },
            validation: null
        }),
        methods: {
            submit() {
                //TODO messages
                this.$v.$reset();
                if (!this.$v.$invalid) {
                    this.validation = null;
                    Api.SingleRequest('login', this.form)
                        .then(() => this.$v.$touch())
                        .success(() => this.$router.push({name: 'index'}))
                        .error((result) => {
                            if (result.validation) {
                                this.validation = result.validation;
                            }
                        })
                        .fire();
                } else {
                    this.$v.$touch();
                }
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
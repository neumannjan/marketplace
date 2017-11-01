<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>Login</h1>

        <form-input class="form-group" :validation="$v.form.login" v-model="form.login" required autofocus>Login
        </form-input>
        <form-input class="form-group" :validation="$v.form.password" v-model="form.password" type="password" required>
            Password
        </form-input>

        <form-select class="form-group" v-model="form.remember">Remember Me</form-select>

        <div class="form-group">
            <button type="submit" class="btn btn-primary" @click="submit">Login</button> <!-- TODO translate -->
            <router-link class="btn btn-link" :to="{ name: 'password-request' }">Forgot Your Password?</router-link>
        </div>
    </div>
</template>

<script>
    import InputComponent from '../widgets/form/input.vue';
    import SelectComponent from '../widgets/form/select.vue';
    import Api from '../../api';

    import {required, minLength} from 'vuelidate/lib/validators';

    import title from './../mixins/title';
    import api, {apiValidator} from './../mixins/api';

    export default {
        mixins: [title, api],
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
                //TODO make sure that we can call the api when the only invalid is the api
                //TODO messages
                this.$v.$reset();
                if (!this.$v.$invalid) {
                    this.$apiCall(Api.SingleRequest('login', this.form))
                        .then(() => this.$v.$touch())
                        .success(() => this.$router.push({name: 'index'}))
                        .fire();
                } else {
                    this.$v.$touch();
                    console.log('form invalid: api not called');
                }
            }
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
                    ...apiValidator('login', 'login')
                },
                password: {
                    required,
                    minLength: minLength(8),
                    ...apiValidator('login', 'password')
                },
            }
        }
    };
</script>
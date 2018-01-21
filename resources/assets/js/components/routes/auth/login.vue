<template>
    <div class="col-flexible">
        <h1>{{ title }}</h1>

        <form id="form-login">
            <form-input class="form-group"
                        label="Login"
                        name="login"
                        :serverValidation="$serverValidationOn('form.login')"
                        :validation="$v.form.login"
                        v-model="form.login"
                        hint="Username or email"
                        required autofocus/>
            <form-input class="form-group"
                        label="Password"
                        name="password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password" required/>

            <form-select class="form-group" v-model="form.remember">Remember Me</form-select>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit">Login</button>
                <!-- TODO translate -->
                <router-link class="btn btn-link" :to="{ name: 'password-email' }">Forgot Your Password?</router-link>
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {minLength, required} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import form from 'JS/components/mixins/form';

    export default {
        mixins: [route, form],
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            isTopLevelRoute: true,
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
                    min: minLength(5),
                },
                password: {
                    required,
                    min: minLength(8),
                },
            }
        }
    };
</script>

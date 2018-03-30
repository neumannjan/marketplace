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
                        autofocus/>
            <form-input class="form-group"
                        label="Password"
                        name="password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password"/>

            <form-select class="form-group" v-model="form.remember">Remember Me</form-select>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit">Login</button>
                <!-- TODO translate -->
                <router-link class="btn btn-link" :to="{ name: 'password-email' }">Forgot Your Password?</router-link>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
    import FormInput from 'JS/components/widgets/form/input.vue';
    import FormSelect from 'JS/components/widgets/form/select.vue';

    import appEvents,{ Events } from "JS/events";

    import {minLength, required} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import FormMixin from 'JS/components/mixins/form';
    import { LoginResponse } from 'JS/api/types';
    import Vue from 'vue';
    import store from 'JS/store';
    import routeGuard from 'JS/components/mixins/route-guard';

    export default Vue.extend({
        mixins: [route, FormMixin, routeGuard('guest', () => !store.state.user)],
        components: {
            FormInput,
            FormSelect
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
                const formMixin: FormMixin = <any>this;
                formMixin.$submitForm('login', 'form', (response: LoginResponse) => {
                    if (response.unread_conversations && response.unread_conversations.length > 0) {
                        appEvents.dispatch(Events.UnreadConversations, response.unread_conversations);
                    }

                    this.$router.push({name: 'index'});
                });
            },
        },
        computed: {
            title(): string {
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
    });
</script>

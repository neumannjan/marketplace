<template>
    <div class="col-flexible">
        <h1>{{ title }}</h1>

        <form id="form-login">
            <form-input class="form-group"
                        :label="translations.form.login"
                        name="login"
                        :serverValidation="$serverValidationOn('form.login')"
                        :validation="$v.form.login"
                        v-model="form.login"
                        :hint="translations.hint.login"
                        autofocus/>
            <form-input class="form-group"
                        :label="translations.form.password"
                        name="password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password"/>

            <form-select class="form-group" v-model="form.remember" name="remember">{{ translations.button.remember }}
            </form-select>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit">{{
                    translations.button.login }}
                </button>
                <router-link class="btn btn-link" :to="{ name: 'password-email' }">{{ translations.button.forgot }}
                </router-link>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
    import FormInput from 'JS/components/widgets/form/input.vue';
    import FormSelect from 'JS/components/widgets/form/select.vue';

    import appEvents, {Events} from "JS/events";

    import {minLength, required} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import FormMixin from 'JS/components/mixins/form';
    import {LoginResponse} from 'JS/api/types';
    import Vue from 'vue';
    import store from 'JS/store';
    import routeGuard from 'JS/components/mixins/route-guard';
    import {TranslationMessages} from 'lang.js';

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
                return this.$store.getters.trans('interface.page.login');
            },
            translations(): TranslationMessages {
                return {
                    form: {
                        login: this.$store.getters.trans('interface.form.login'),
                        password: this.$store.getters.trans('interface.form.password'),
                    },
                    hint: {
                        login: this.$store.getters.trans('interface.hint.login')
                    },
                    button: {
                        login: this.$store.getters.trans('interface.button.login'),
                        remember: this.$store.getters.trans('interface.button.remember-me'),
                        forgot: this.$store.getters.trans('interface.button.forgot-password'),
                    }
                }
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

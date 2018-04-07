<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>{{ title }}</h1>

        <form id="form-register">
            <form-input class="form-group"
                        :label="translations.form.username"
                        name="username"
                        :serverValidation="$serverValidationOn('form.username')"
                        :validation="$v.form.username"
                        v-model="form.username"
                        autofocus/>
            <form-input class="form-group"
                        :label="translations.form.email"
                        name="email"
                        :serverValidation="$serverValidationOn('form.email')"
                        :validation="$v.form.email"
                        v-model="form.email"
                        type="email"/>
            <form-input class="form-group"
                        :label="translations.form.display_name"
                        name="display_name"
                        :serverValidation="$serverValidationOn('form.display_name')"
                        :validation="$v.form.display_name"
                        v-model="form.display_name"/>
            <form-input class="form-group"
                        :label="translations.form.password"
                        name="password"
                        :serverValidation="$serverValidationOn('form.password')"
                        :validation="$v.form.password"
                        v-model="form.password"
                        type="password"/>
            <form-input class="form-group"
                        :label="translations.form.confirm"
                        name="password_confirmation"
                        :serverValidation="$serverValidationOn('form.password_confirmation')"
                        :validation="$v.form.password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"/>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit">{{
                    translations.button.register }}
                </button>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {email, maxLength, minLength, required, sameAs} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import form from 'JS/components/mixins/form';
    import store from 'JS/store';
    import routeGuard from 'JS/components/mixins/route-guard';
    import {TranslationMessages} from 'lang.js';
    import {Component, mixins} from 'JS/components/class-component';

    @Component({
        name: 'register-route',
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        validations: {
            form: {
                username: {
                    required,
                    min: minLength(5),

                    slug(value: string) {
                        if(value === '') return true;

                        return (value.match(/^[a-zA-Z0-9-_]+$/) !== null);
                    }
                },
                email: {
                    required,
                    email,
                },
                display_name: {
                    max: maxLength(50),
                },
                password: {
                    required,
                    min: minLength(8),

                    containsNonNumeric(value: string) {
                        if (value === '') return true;

                        return value.match(/[^0-9]/) !== null;
                    },

                    containsNumeric(value: string) {
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
    })
    export default class Register extends mixins(route, form, routeGuard('guest', () => !store.state.user)) {
        isTopLevelRoute: boolean = true;
        form = {
            username: "",
            email: "",
            display_name: "",
            password: "",
            password_confirmation: ""
        }

        submit() {
            this.$submitForm('register', 'form', () => this.$router.push({name: 'index'}));
        }

        get title() {
            return this.$store.getters.trans('interface.page.register');
        }

        get translations(): TranslationMessages {
            return {
                form: {
                    username: this.$store.getters.trans('interface.form.username'),
                    email: this.$store.getters.trans('interface.form.email'),
                    display_name: this.$store.getters.trans('interface.form.display_name'),
                    password: this.$store.getters.trans('interface.form.password'),
                    confirm: this.$store.getters.trans('interface.form.password_confirmation'),
                },
                button: {
                    register: this.$store.getters.trans('interface.button.register'),
                }
            }
        }
    }
</script>

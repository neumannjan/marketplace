<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>{{ title }}</h1>

        <form id="form-register">
            <form-input class="form-group"
                        label="Username"
                        name="username"
                        :serverValidation="$serverValidationOn('form.username')"
                        :validation="$v.form.username"
                        v-model="form.username"
                        required autofocus/>
            <form-input class="form-group"
                        label="E-mail"
                        name="email"
                        :serverValidation="$serverValidationOn('form.email')"
                        :validation="$v.form.email"
                        v-model="form.email"
                        type="email" required/>
            <form-input class="form-group"
                        label="Display Name"
                        name="display_name"
                        :serverValidation="$serverValidationOn('form.display_name')"
                        :validation="$v.form.display_name"
                        v-model="form.display_name"/>
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
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit">Register</button>
                <!-- TODO translate -->
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {email, maxLength, minLength, required, sameAs} from 'vuelidate/lib/validators';

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
                    max: maxLength(50),
                },
                password: {
                    required,
                    min: minLength(8),
                    containsNonNumeric(value) {
                        if (value === '') return true;

                        return value.match(/[^0-9]/) !== null;
                    },
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

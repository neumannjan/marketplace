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
                        type="email" autofocus/>

            <div class="form-group">
                <button type="submit" id="submit" class="btn btn-primary" @click.prevent="submit">Send Password Reset
                    Link
                </button>
                <!-- TODO translate -->
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {email, required} from 'vuelidate/lib/validators';

    import store from 'JS/store';
    import route from 'JS/components/mixins/route';
    import form from 'JS/components/mixins/form';
    import routeGuard from 'JS/components/mixins/route-guard';

    export default {
        mixins: [route, form, routeGuard('guest', () => !store.state.user)],
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            form: {
                email: "",
            }
        }),
        methods: {
            submit() {
                this.$submitForm('password-email', 'form', () => this.$router.push({name: 'index'}));
            },
        },
        computed: {
            title() {
                return 'Reset Password';
            },
        },
        validations: {
            form: {
                email: {
                    required,
                    email,
                },
            }
        }
    };
</script>

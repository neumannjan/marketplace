<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>Reset Password</h1>

        <form id="form-register">
            <form-input class="form-group"
                        label="E-mail"
                        name="email"
                        :serverValidation="$serverValidationOn('form.email')"
                        :validation="$v.form.email"
                        v-model="form.email"
                        type="email" required autofocus></form-input>

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
    import InputComponent from '../../widgets/form/input.vue';
    import SelectComponent from '../../widgets/form/select.vue';

    import {required, minLength, email, sameAs} from 'vuelidate/lib/validators';

    import title from './../../mixins/title';
    import form from './../../mixins/form';

    export default {
        mixins: [title, form],
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

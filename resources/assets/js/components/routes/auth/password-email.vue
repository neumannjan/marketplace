<template>
    <div class="col-sm-8 col-md-6 col-lg-4">
        <h1>{{ title }}</h1>

        <form id="form-register">
            <form-input class="form-group"
                        :label="translations.form.email"
                        name="email"
                        :serverValidation="$serverValidationOn('form.email')"
                        :validation="$v.form.email"
                        v-model="form.email"
                        type="email" autofocus/>

            <div class="form-group">
                <button type="submit" id="submit" class="btn btn-primary" @click.prevent="submit">{{ translations.button
                    }}
                </button>
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
                //@ts-ignore
                this.$submitForm('password-email', 'form', () => this.$router.push({name: 'index'}));
            },
        },
        computed: {
            title() {
                return this.$store.getters.trans('interface.page.password-email');
            },
            translations() {
                return {
                    form: {
                        email: this.$store.getters.trans('interface.form.email'),
                    },
                    button: this.$store.getters.trans('interface.button.password-email'),
                }
            }
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

<template>
    <div class="row">
        <form id="form-settings"
              ref="form"
              class="col-md-12 col-lg-8 mx-auto"
              @submit.prevent="submit"
              autocomplete="new-password">
            <div class="mb-5">
                <h2 class="h3 mb-2">{{ translations.form.basic }}</h2>
                <form-input class="form-group"
                            :label="translations.form.username"
                            name="username"
                            v-model="form.username"
                            disabled/>
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
            </div>

            <div class="mb-5">
                <h2 class="h3 mb-2">{{ translations.form.image }}</h2>
                <file-select name="image"
                             accept="image/*"
                             :hint="translations.hint.empty"
                             :error-label="translations.form.image"
                             :server-validation="$serverValidationOn('form.images')"
                             :validation="$v.form.images"
                             v-model="form.images">
                    <button slot="append" class="btn btn-danger" @click.prevent="clearImage()"
                            :title="translations.button.clearimage">
                        <icon name="times"/>
                    </button>
                </file-select>
                <form-select :disabled="form.images && form.images.length > 0" name="remove_image" v-model="form.remove_image">
                    {{ translations.button.removeimage }}
                </form-select>
            </div>

            <div class="mb-5">
                <h2 class="h3 mb-2">{{ translations.form.password_change }}</h2>
                <form-input class="form-group"
                            :label="translations.form.password"
                            name="password"
                            :serverValidation="$serverValidationOn('form.password')"
                            :validation="$v.form.password"
                            v-model="form.password"
                            :hint="translations.hint.empty"
                            type="password"/>
                <form-input class="form-group"
                            :label="translations.form.confirm"
                            name="password_confirmation"
                            :serverValidation="$serverValidationOn('form.password_confirmation')"
                            :validation="$v.form.password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"/>
            </div>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary">{{ translations.button.update }}</button>
            </div>
        </form>
    </div>
</template>

<script lang="ts">
    import { Vue, Component, mixins } from "JS/components/class-component";
    import route from "JS/components/mixins/route";
    import routeGuard from "JS/components/mixins/route-guard";
    import form from 'JS/components/mixins/form';
    import store from "JS/store";
    import { User } from "JS/api/types";
    import { TranslationMessages } from "lang.js";
    import notifications from "JS/notifications";

    import {email, maxLength, minLength, required, sameAs} from 'vuelidate/lib/validators';
    import withParams from "vuelidate/lib/withParams";

    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';
    import FileSelect from 'JS/components/widgets/form/file-select.vue';

    import "vue-awesome/icons/times";

    interface FormData {
        username: string,
        email: string,
        display_name: string,
        password: string,
        password_confirmation: string,
        images: FileList | undefined,
        remove_image: boolean
    }

    interface FormDataContainer {
        form: FormData,
    }

    interface Response {
        user: User,
        password?: boolean,
        image?: boolean
    }

    @Component({
        name: 'user-settings-route',
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent,
            FileSelect
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
                images: {
                    image(images: FileList | undefined) {
                        if(images === undefined) {
                            return true;
                        }

                        for (let image of Array.from(images)) {
                            if (!image.type.startsWith('image/'))
                                return false;
                        }

                        return true;
                    },
                    maxArray: withParams({
                        max: 1
                    }, (images: FileList | undefined) => {
                        return images === undefined || images.length <= 1;
                    })
                },
                password: {
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
                    required(value: string | null) {
                        const password = ((this as any) as FormDataContainer).form.password;

                        return !password || password === "" || (typeof value === 'string' && value !== "");
                    },
                    confirmed: sameAs('password'),
                },
            }
        }
    })
    export default class UserSettings
        extends mixins(route, form, routeGuard('auth', () => !!store.state.user))
        implements FormDataContainer {

        isTopLevelRoute = true;

        form: FormData = {
            username: "",
            email: "",
            display_name: "",
            password: "",
            password_confirmation: "",
            images: undefined,
            remove_image: false
        };
        
        get title() {
            return this.$store.getters.trans('interface.page.user-settings');
        }

        get translations(): TranslationMessages {
            return {
                form: {
                    basic: this.$store.getters.trans('interface.form.user-information'),
                    username: this.$store.getters.trans('interface.form.username'),
                    email: this.$store.getters.trans('interface.form.email'),
                    display_name: this.$store.getters.trans('interface.form.display_name'),
                    password_change: this.$store.getters.trans('interface.form.password_change'),
                    password: this.$store.getters.trans('interface.form.password'),
                    confirm: this.$store.getters.trans('interface.form.password_confirmation'),
                    image: this.$store.getters.trans('interface.form.profile-image'),
                },
                button: {
                    update: this.$store.getters.trans('interface.button.update-profile'),
                    clearimage: this.$store.getters.trans('interface.button.clear-image'),
                    removeimage: this.$store.getters.trans('interface.button.remove-profile-image'),
                },
                hint: {
                    empty: this.$store.getters.trans('interface.hint.empty_change'),
                }
            }
        }

        submit() {
            const formData = new FormData(this.$refs.form as HTMLFormElement);
            formData.delete('username');

            if(this.form.images && this.form.images.length > 0)
                formData.delete('remove_image');

            if(this.form.password === '')
                formData.delete('password');

            if(formData.has('remove_image'))
                formData.set('remove_image', formData.get('remove_image') === 'true' ? '1' : '0');

            this.$submitForm('user-settings', 'form', (response: Response) => {
                notifications.showNotification({
                    type: 'success',
                    message: this.$store.getters.trans('interface.notification.user-settings.success'),
                });

                if(response.password !== undefined) {
                    notifications.showNotification({
                        type: response.password ? 'success' : 'danger',
                        message: this.$store.getters.trans(`interface.notification.user-settings.password.${response.password ? 'success' : 'failure'}`),
                    })
                }

                if(response.image !== undefined) {
                    notifications.showNotification({
                        type: response.image ? 'success' : 'danger',
                        message: this.$store.getters.trans(`interface.notification.user-settings.image.${response.image ? 'success' : 'failure'}`),
                    })
                }

                this.$router.push({name: 'me'});
            }, formData, false, () => {
                notifications.showNotification({
                    type: 'danger',
                    message: this.$store.getters.trans('interface.notification.user-settings.failure'),
                });
            });
        }

        clearImage() {
            this.form = {...this.form, images: undefined};
        }

        created() {
            const user: User | null = this.$store.state.user;
            if(user) {
                this.form.username = user.username;
                this.form.email = user.email;
                this.form.display_name = user.display_name;
            }
        }
    }
</script>

<template>
    <div>
        <h1>{{ title }}</h1>

        <form id="form-offer">
            <form-input class="form-group"
                        label="What are you selling?"
                        error-label="Offer name"
                        name="name"
                        :server-validation="$serverValidationOn('form.name')"
                        :validation="$v.form.name"
                        v-model="form.name"
                        required autofocus/>
            <form-input class="form-group"
                        label="Describe the offer a little more"
                        error-label="Description"
                        name="description"
                        :textarea="5"
                        :server-validation="$serverValidationOn('form.description')"
                        :validation="$v.form.description"
                        v-model="form.description"/>

            <label for="price">
                <slot>Price</slot>
            </label>
            <div class="input-group">
                <input id="price"
                       class="form-control"
                       @blur="$v.form.price.$touch()"
                       type="text"
                       name="price"
                       v-model="form.price"/>
                <select class="custom-select"
                        id="currency"
                        name="currency"
                        title="Currency"
                        v-model="form.currency">
                    <option value="0" selected>Currency</option>
                    <option v-for="(currency, key) in currencies" :key="key" :value="currency[0]">{{currency[0]}}
                    </option>
                </select>
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
        name: 'offer-form-route',
        mixins: [route, form],
        components: {
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            form: {
                name: "",
                description: "",
                price: "",
                currency: 0,
            }
        }),
        methods: {
            submit() {
                this.$submitForm('login', 'form', () => this.$router.push({name: 'index'}));
            },
        },
        computed: {
            title() {
                //TODO
                return 'Create offer';
            },
            currencies() {
                return this.$store.state.currencies.sort((c1, c2) => {
                    const a = c1[0];
                    const b = c2[0];
                    return (a < b) ? -1 : (a > b) ? 1 : 0;
                })
            }
        },
        validations: {
            form: {
                name: {
                    required,
                    min: minLength(3),
                },
                description: {
                    min: minLength(5),
                },
                price: {},
            }
        }
    };
</script>

<style scoped>
    .custom-select {
        flex-grow: 0;
        flex-shrink: 0;
        width: auto;
    }
</style>
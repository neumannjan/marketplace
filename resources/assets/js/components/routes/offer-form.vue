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
                <choices :items="choicesCurrencies"
                         v-model="form.currency">
                </choices>
            </div>
            <div class="h3 my-3 price">{{ price }}</div>
        </form>
    </div>
</template>

<script>
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {minLength, required} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import form from 'JS/components/mixins/form';

    import {cached} from 'JS/store';
    import Choices from "JS/components/widgets/form/choices";

    export default {
        name: 'offer-form-route',
        mixins: [route, form],
        components: {
            Choices,
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            currencies: {},
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
            price() {
                if (typeof this.form.currency !== "string" || (this.form.price !== 0 && !this.form.price))
                    return '';

                const val = new Intl.NumberFormat(this.$store.state.locale,
                    {style: 'currency', currency: this.form.currency}).format(this.form.price);

                return val.substr(0, 3) !== 'NaN' ? val : '';
            },
            choicesCurrencies() {
                const currencies = Object.keys(this.currencies).map((currency, index) => ({
                    value: currency,
                    label: currency,
                    id: index
                }));

                currencies.push({
                    value: 0,
                    label: 'Currency', //TODO translate
                    selected: true,
                    placeholder: true,
                });

                return currencies;
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
        },
        async mounted() {
            this.currencies = (await cached()).currencies;
        }
    };
</script>

<style scoped>
    .custom-select {
        flex-grow: 0;
        flex-shrink: 0;
        width: auto;
    }

    .price {
        word-wrap: break-word;
    }

</style>
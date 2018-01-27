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
                       autocomplete="off"
                       ref="price"
                       :class="['form-control', {'is-invalid': priceError, 'is-valid': priceValid}]"
                       @input="onPriceInput"
                       type="text"
                       name="price"/>
                <choices :items="choicesCurrencies"
                         :elem-class="{'is-invalid': priceError, 'is-valid': priceValid}"
                         @input="touchPrice"
                         v-model="form.currency">
                </choices>
            </div>
            <validation-message label="Price"
                                :input="form.price"
                                :server-validation="$serverValidationOn('form.price')"
                                @error="e => setObjArg('errors', 'price', e)"
                                @valid="v => setObjArg('valids', 'price', v)"
                                :validation="$v.form.price"/>
            <validation-message label="Currency"
                                :input="form.currency"
                                :server-validation="$serverValidationOn('form.currency')"
                                @error="e => setObjArg('errors', 'currency', e)"
                                @valid="v => setObjArg('valids', 'currency', v)"
                                :validation="$v.form.currency"/>
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
    import ValidationMessage from "JS/components/widgets/form/validation-message";

    import Cleave from 'cleave.js';

    export default {
        name: 'offer-form-route',
        mixins: [route, form],
        components: {
            ValidationMessage,
            Choices,
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            currencies: {},
            errors: {},
            valids: {},
            priceCleave: null,
            form: {
                name: "",
                description: "",
                price: "",
                currency: 0,
            }
        }),
        methods: {
            touchPrice() {
                this.$v.form.price.$touch();
                this.$v.form.currency.$touch();
            },
            onPriceInput($event) {
                this.touchPrice();
                this.form.price = parseFloat(this.priceCleave.getRawValue());
            },
            submit() {
                this.$submitForm('login', 'form', () => this.$router.push({name: 'index'}));
            },
            setObjArg(objName, key, value) {
                const obj = this[objName];
                this[objName] = {...obj, [key]: value};
            }
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
            },
            priceValid() {
                return this.valids.price && this.valids.currency;
            },
            priceError() {
                return this.errors.price || this.errors.currency;
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
                price: {
                    required(val) {
                        return (val || val === 0) && !isNaN(val);
                    },
                    numeric(val) {
                        return typeof val === "number";
                    }
                },
                currency: {
                    required(value) {
                        return (value && value.match(/[a-zA-Z]/) !== null) || (this.form.price === 0);
                    },
                },
            }
        },
        async mounted() {
            this.priceCleave = new Cleave(this.$refs.price, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralIntegerScale: 30,
                numeralDecimalScale: 8,
                numeralPositiveOnly: true,

            });
            this.currencies = (await cached()).currencies;
        },
        beforeDestroy() {
            this.priceCleave.destroy();
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
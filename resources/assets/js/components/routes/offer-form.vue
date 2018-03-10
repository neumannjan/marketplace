<template>
    <div>
        <h1>{{ title }}</h1>

        <form id="form-offer" ref="form">
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

            <div class="form-group">
                <label for="price">
                    <slot>Price</slot>
                </label>
                <div class="input-group">
                    <input id="price"
                           autocomplete="off"
                           ref="price"
                           :class="['form-control', {'is-invalid': priceError, 'is-valid': priceValid}]"
                           @input="onPriceInput"
                           type="tel"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           formnovalidate
                           name="price"/>
                    <choices :items="choicesCurrencies"
                             :elem-class="{'is-invalid': priceError, 'is-valid': priceValid}"
                             @input="touchPrice"
                             name="currency"
                             v-model="form.currency">
                    </choices>
                </div>
                <validation-message label="Price"
                                    :input="form.price"
                                    @error="e => setObjArg('errors', 'price', e)"
                                    @valid="v => setObjArg('valids', 'price', v)"
                                    :server-validation="$serverValidationOn('form.price')"
                                    :validation="$v.form.price"/>
                <validation-message label="Currency"
                                    :input="form.currency"
                                    @error="e => setObjArg('errors', 'currency', e)"
                                    @valid="v => setObjArg('valids', 'currency', v)"
                                    :server-validation="$serverValidationOn('form.currency')"
                                    :validation="$v.form.currency"/>
                <div class="h3 my-3 price" v-if="price">{{ price }}</div>
            </div>

            <file-select name="images[]"
                         accept="image/*"
                         multiple
                         label="Add some images"
                         error-label="File"
                         :server-validation="$serverValidationOn('form.images')"
                         :validation="$v.form.images"
                         @input="onFileInput"/>

            <div v-if="imageOrder && imageOrder.length > 0" class="form-group">
                <div class="mb-2">Reorder the images (use drag & drop)</div>
                <draggable class="d-flex flex-wrap thumbnail-container" v-model="imageOrder">
                    <placeholder-img v-for="(image) of imageOrder"
                                     :src="image.src"
                                     :key="image.key"
                                     class="thumbnail-wrapper p-2"
                                     img-class="w-100 h-100 rounded"
                                     :img-style="{objectFit: 'cover'}"/>
                </draggable>
            </div>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit(false)">Publish
                </button>
                <!--TODO draft editing, existing image editing (drag / drop existing and new)
                <button id="submit-draft" type="submit" class="btn btn-warning" @click.prevent="submit(true)">Save as draft</button>-->
            </div>
        </form>
    </div>
</template>

<script>
    import InputComponent from 'JS/components/widgets/form/input.vue';
    import SelectComponent from 'JS/components/widgets/form/select.vue';

    import {minLength, required} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import routeGuard from 'JS/components/mixins/route-guard';
    import form from 'JS/components/mixins/form';
    import store from 'JS/store';
    import Vue from 'vue';

    import {cached} from 'JS/store';
    import Choices from "JS/components/widgets/form/choices.vue";
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";

    //@ts-ignore
    import Cleave from 'cleave.js';
    import FileSelect from "JS/components/widgets/form/file-select.vue";

    //@ts-ignore
    import Draggable from 'vuedraggable';
    import PlaceholderImg from "JS/components/widgets/image/placeholder-img.vue";

    export default Vue.extend({
        name: 'offer-form-route',
        mixins: [
            route,
            routeGuard('auth', () => store.state.is_authenticated),
            form
        ],
        components: {
            PlaceholderImg,
            FileSelect,
            ValidationMessage,
            Choices,
            Draggable,
            'form-input': InputComponent,
            'form-select': SelectComponent
        },
        data: () => ({
            currencies: {},
            errors: {},
            valids: {},

            /** @type {any} */
            priceCleave: null,

            /** @type {any} */
            form: {
                name: "",
                description: "",
                /** @type {string | number} */
                price: "",
                currency: false,
                /** @type {Array | FileList} */
                images: [],
            },
            /** @type {Array} */
            imageOrder: []
        }),
        methods: {
            touchPrice() {
                //@ts-ignore
                this.$v.form.price.$touch();
                //@ts-ignore
                this.$v.form.currency.$touch();
            },
            onPriceInput() {
                this.touchPrice();
                this.form = {...this.form, price: parseFloat(this.priceCleave.getRawValue())};
            },
            /** @param {FileList} f */
            onFileInput(f) {
                this.form = {...this.form, images: f};

                /** @type {any} */
                let imageOrder = [];

                for (let [index, image] of Array.from(f).entries()) {
                    const entry = {src: null, new: true, index: index, key: `new|${index}`};
                    imageOrder.push(entry);

                    const reader = new FileReader();
                    reader.addEventListener("load", () => {
                        const is = imageOrder === this.imageOrder;
                        const i = imageOrder.indexOf(entry);

                        if (i >= 0) {
                            imageOrder[i].src = reader.result;
                        }

                        if (is) {
                            this.imageOrder = imageOrder;
                        }
                    });
                    reader.readAsDataURL(image);
                }

                this.imageOrder = imageOrder;
            },
            submit(asDraft = false) {
                //@ts-ignore
                const formData = new FormData(this.$refs.form);
                formData.set('price', this.form.price);
                formData.set('status', asDraft ? 0 : 1);

                const imageOrder = Object.values(this.imageOrder).map(val => ({
                    new: val.new,
                    index: val.index
                }));

                formData.set('imageOrder', JSON.stringify(imageOrder));

                /**
                 * @param {object} result
                 */
                const onSubmitSuccess = result => {
                    this.form = {};

                    if (result && result.id)
                        this.$router.replace({name: 'offer', params: {id: result.id}});
                    else
                        this.$router.replace({name: 'index'});
                };

                this.$submitForm('offer-create', 'form', onSubmitSuccess, formData, asDraft);
            },

            /**
             * @param {string} objName
             * @param {string} key
             * @param {any} value
             */
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
                /** @type {Array} */
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
            },
            formEmpty() {
                for (let field of Object.values(this.form)) {
                    if (field) return false;
                }

                return true;
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
                    /**
                     * @param {number} val
                     */
                    required(val) {
                        return (val || val === 0) && !isNaN(val);
                    },

                    /**
                     * @param {number} val
                     */
                    numeric(val) {
                        return typeof val === "number";
                    }
                },
                currency: {

                    /**
                     * @param {string} value
                     */
                    required(value) {
                        return (value && value.match(/[a-zA-Z]/) !== null) || (this.form.price === 0);
                    },
                },
                images: {

                    /**
                     * @param {Array} value
                     */
                    required(value) {
                        return value && value.length > 0;
                    },

                    /**
                     * @param {Array} images
                     */
                    image(images) {
                        for (let image of images) {
                            if (!image.type.startsWith('image/'))
                                return false;
                        }

                        return true;
                    }
                }
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
        },
        beforeRouteLeave(to, from, next) {
            if (this.formEmpty || window.confirm('Are you sure you want to leave? You have unsaved changes!')) {
                next()
            } else {
                next(false)
            }
        }
    });
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    .custom-select {
        flex-grow: 0;
        flex-shrink: 0;
        width: auto;
    }

    .price {
        word-wrap: break-word;
    }

    .thumbnail-container {
        margin: #{-1*map_get($spacers, 2)};
    }

    .thumbnail-wrapper {
        width: 150px;
        height: 150px;
        position: relative;
    }
</style>
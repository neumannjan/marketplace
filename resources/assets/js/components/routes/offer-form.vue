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

<script lang="ts">
    import FormInput from 'JS/components/widgets/form/input.vue';
    import FormSelect from 'JS/components/widgets/form/select.vue';

    import {minLength, required} from 'vuelidate/lib/validators';

    import route from 'JS/components/mixins/route';
    import routeGuard from 'JS/components/mixins/route-guard';
    import form from 'JS/components/mixins/form';
    import store from 'JS/store';
    import {cached} from 'JS/store';
    import { mixins } from 'JS/components/class-component';
    import Vue,{ Component } from 'JS/components/class-component';

    import Choices from "JS/components/widgets/form/choices.vue";
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";

    //@ts-ignore
    import Cleave from 'cleave.js';
    import FileSelect from "JS/components/widgets/form/file-select.vue";

    //@ts-ignore
    import Draggable from 'vuedraggable';
    import PlaceholderImg from "JS/components/widgets/image/placeholder-img.vue";
    import { Currencies, Offer } from 'JS/api/types';
    import { Route, RawLocation } from 'vue-router';

    interface FormData {
        name?: string;
        description?: string;
        price?: string | number;
        currency?: string | false;
        images?: Array<any> | FileList;
    }

    interface ImageOrderInstance {
        src: string | null,
        new: boolean,
        index: number,
        key: string
    }

    interface ChoicesCurrencies {
        value: string | number,
        label: string,
        id?: number,
        selected?: boolean
        placeholder?: boolean
    }

    @Component({
        name: 'offer-form-route',
        components: {
            PlaceholderImg,
            FileSelect,
            ValidationMessage,
            Choices,
            Draggable,
            FormInput,
            FormSelect
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
                    required(val: number) {
                        return (val || val === 0) && !isNaN(val);
                    },
                    numeric(val: number) {
                        return typeof val === "number";
                    }
                },
                currency: {
                    required(value: string) {
                        return (value && value.match(/[a-zA-Z]/) !== null) || ((<any>this).form.price === 0);
                    },
                },
                images: {
                    required(value: any[]) {
                        return value && value.length > 0;
                    },
                    image(images: any[]) {
                        for (let image of images) {
                            if (!image.type.startsWith('image/'))
                                return false;
                        }

                        return true;
                    }
                }
            }
        }
    })
    export default class OfferFormRoute extends mixins(route, routeGuard('auth', () => store.state.is_authenticated), form) {
        currencies: Currencies = {};
        errors: {[index: string]: string | null} = {};
        valids: {[index: string]: boolean} = {};

        priceCleave: any | null;

        form: FormData = {
            name: "",
            description: "",
            price: "",
            currency: false,
            images: []
        }

        imageOrder: ImageOrderInstance[] = [];

        touchPrice() {
            this.$v.form.price.$touch();
            this.$v.form.currency.$touch();
        }

        onPriceInput() {
            this.touchPrice();
            this.form = {...this.form, price: parseFloat(this.priceCleave.getRawValue())};
        }

        onFileInput(f: FileList) {
            this.form = {...this.form, images: f};

            let imageOrder: ImageOrderInstance[] = [];

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
        }

        submit(asDraft = false) {
            const formData = new FormData(this.$refs.form as HTMLFormElement);
            formData.set('price', this.form.price ? this.form.price.toString() : '0');
            formData.set('status', asDraft ? '0' : '1');

            const imageOrder = Object.values(this.imageOrder).map(val => ({
                new: val.new,
                index: val.index
            }));

            formData.set('imageOrder', JSON.stringify(imageOrder));

            const onSubmitSuccess = (result: Offer) => {
                this.form = {};

                if (result && result.id)
                    this.$router.replace({name: 'offer', params: {id: result.id.toString()}});
                else
                    this.$router.replace({name: 'index'});
            };

            this.$submitForm('offer-create', 'form', onSubmitSuccess, formData, asDraft);
        }

        setObjArg(objName: string, key: string, value: any) {
            const obj = (<any>this)[objName];
            (<any>this)[objName] = {...obj, [key]: value};
        }
        
        get title() {
            //TODO
            return 'Create offer';
        }

        get price() {
            if (typeof this.form.currency !== "string" || (this.form.price !== 0 && !this.form.price))
                return '';

            const price = typeof this.form.price === 'number' ? this.form.price : parseFloat(this.form.price);

            const val = new Intl.NumberFormat(this.$store.state.locale,
                {style: 'currency', currency: this.form.currency}).format(price);

            return val.substr(0, 3) !== 'NaN' ? val : '';
        }

        get choicesCurrencies(): ChoicesCurrencies[] {
            const currencies: ChoicesCurrencies[] = Object.keys(this.currencies).map((currency, index) => ({
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

        get priceValid() {
            return this.valids.price && this.valids.currency;
        }

        get priceError() {
            return this.errors.price || this.errors.currency;
        }

        get formEmpty() {
            for (let field of Object.values(this.form)) {
                if (field) return false;
            }

            return true;
        }
        
        async mounted() {
            this.priceCleave = new Cleave(this.$refs.price, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralIntegerScale: 30,
                numeralDecimalScale: 8,
                numeralPositiveOnly: true,

            });
            this.currencies = (await cached()).currencies;
        }

        beforeDestroy() {
            this.priceCleave.destroy();
        }

        beforeRouteLeave(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: any) => any) | void) => void) {
            if (this.formEmpty || window.confirm('Are you sure you want to leave? You have unsaved changes!')) {
                next()
            } else {
                next(false)
            }
        }
    }
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
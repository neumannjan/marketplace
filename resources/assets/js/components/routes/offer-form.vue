<template>
    <div>
        <h1>{{ title }}</h1>

        <form id="form-offer" ref="form">
            <form-input class="form-group"
                        :label="translations.form.extended.name"
                        :error-label="translations.form.name"
                        name="name"
                        v-focus
                        :server-validation="$serverValidationOn('form.name')"
                        :validation="$v.form.name"
                        v-model="form.name"
                        autofocus/>
            <form-input class="form-group"
                        :label="translations.form.extended.description"
                        :error-label="translations.form.description"
                        name="description"
                        :textarea="5"
                        :server-validation="$serverValidationOn('form.description')"
                        :validation="$v.form.description"
                        v-model="form.description"/>

            <div class="form-group">
                <label for="price">
                    {{ translations.form.price }}
                </label>
                <div class="input-group">
                    <input id="price"
                           autocomplete="off"
                           ref="price"
                           :class="['form-control', {'is-invalid': priceError, 'is-valid': priceValid}]"
                           :value="form.price"
                           :maxlength="15"
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
                <validation-message :label="translations.form.price"
                                    :input="form.price"
                                    @error="e => setObjArg('errors', 'price', e)"
                                    @valid="v => setObjArg('valids', 'price', v)"
                                    :server-validation="$serverValidationOn('form.price')"
                                    :validation="$v.form.price"/>
                <validation-message :label="translations.form.currency"
                                    :input="form.currency"
                                    @error="e => setObjArg('errors', 'currency', e)"
                                    @valid="v => setObjArg('valids', 'currency', v)"
                                    :server-validation="$serverValidationOn('form.currency')"
                                    :validation="$v.form.currency"/>
                <div class="h3 my-3 price" v-if="price">{{ price }}</div>
            </div>

            <file-select name="images[]"
                         v-if="!resettingFileInput"
                         accept="image/*"
                         multiple
                         :label="translations.form.extended.images"
                         :error-label="translations.form.images"
                         :server-validation="$serverValidationOn('form.images')"
                         :validation="$v.form.images"
                         @input="onFileInput">
                <button slot="append" v-if="offer" class="btn btn-danger" @click.prevent="resetImageOrder()"
                        :title="translations.button.revert">
                    <icon name="refresh"/>
                </button>
            </file-select>

            <div v-if="imageOrder && imageOrder.length > 0" class="form-group">
                <div class="mb-2">{{ translations.form.reorder }}</div>
                <draggable class="d-flex flex-wrap thumbnail-container" v-model="imageOrder">
                    <div v-for="(image) of imageOrder"
                         :key="image.key"
                         class="thumbnail-wrapper p-2">
                        <placeholder-img :src="image.src"
                                         class="w-100 h-100 rounded thumbnail-border"
                                         img-class="w-100 h-100"
                                         :img-style="{objectFit: 'cover'}"/>
                        <button v-if="!image.new"
                                @click="removeExistingMessage(image.id)"
                                type="button"
                                class="btn badge badge-float badge-danger"
                                :aria-label="translations.button.close">
                            <icon name="times" :label="translations.button.close"/>
                        </button>
                    </div>
                </draggable>
            </div>

            <div class="form-group">
                <button id="submit" type="submit" class="btn btn-primary" @click.prevent="submit(false)">
                    {{ translations.button.publish }}
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

    import {maxLength, minLength, required} from 'vuelidate/lib/validators';
    import withParams from 'vuelidate/lib/withParams';

    import route from 'JS/components/mixins/route';
    import routeGuard from 'JS/components/mixins/route-guard';
    import form from 'JS/components/mixins/form';
    import store from 'JS/store';
    import {Component, mixins, Prop, Vue, Watch} from 'JS/components/class-component';
    import api from 'JS/api';

    import Choices from "JS/components/widgets/form/choices.vue";
    import ValidationMessage from "JS/components/widgets/form/validation-message.vue";
    //@ts-ignore
    import Cleave from 'cleave.js';
    import FileSelect from "JS/components/widgets/form/file-select.vue";
    //@ts-ignore
    import Draggable from 'vuedraggable';
    import PlaceholderImg from "JS/components/widgets/image/placeholder-img.vue";
    import {Currencies, Offer} from 'JS/api/types';
    import {RawLocation, Route} from 'vue-router';
    import routeFetch from 'JS/components/mixins/route-fetch';
    import {getImageFileThumbnailDataURL} from 'JS/lib/helpers';
    import {TranslationMessages} from 'lang.js';

    import 'vue-awesome/icons/times';
    import 'vue-awesome/icons/refresh';

    interface FormData {
        name?: string;
        description?: string;
        price?: string | number;
        currency?: string | 0;
        images?: File[] | FileList;
    }

    interface ImageOrderInstance {
        src: string | undefined,
        new: boolean,
        id: number,
        key: string
    }

    interface ChoicesCurrencies {
        value: string | number,
        label: string,
        id?: number,
        selected?: boolean
        placeholder?: boolean
    }

    interface OfferFormRouteInterface {
        form: FormData;
        offer: Offer | null;
        formModified: boolean;
        imageOrder: ImageOrderInstance[];
        errors: { [index: string]: string | null };
        valids: { [index: string]: boolean };
    }

    const emptyOfferForm = (): OfferFormRouteInterface => ({
        form: {
            name: "",
            description: "",
            price: "",
            currency: store.state.currency_default,
            images: []
        },
        offer: null,
        formModified: false,
        imageOrder: [],
        errors: {},
        valids: {},
    });

    function fetchOffer(params: { id?: number }): Promise<OfferFormRouteInterface> {
        if (params.id === undefined) {
            return Promise.resolve(emptyOfferForm());
        } else {
            return api.requestSingle<Offer>('offer', {
                scope: store.getters.scope.offer,
                id: params.id
            })
                .then(offer => {
                    return {
                        form: {
                            name: offer.name,
                            description: offer.description,
                            price: offer.price_value,
                            currency: offer.currency,
                            images: []
                        },
                        offer: offer,
                        formModified: false,
                        imageOrder: [],
                        errors: {},
                        valids: {}
                    }
                })
        }
    }

    async function afterFetch(vm: Vue) {
        vm.$v.$reset();
        await vm.$nextTick();
        (vm as OfferFormRoute).formModified = false;
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
                    max: maxLength(50),
                },
                description: {
                    min: minLength(5),
                    max: maxLength(2000),
                },
                price: {
                    required(val: number) {
                        return (val || val === 0) && !isNaN(val) && isFinite(val);
                    },
                    numeric(val: number) {
                        return typeof val === "number";
                    },
                    max: maxLength(15)
                },
                currency: {
                    required(value: string) {
                        return (value && value.match(/[a-zA-Z]/) !== null) || ((<any>this).form.price === 0);
                    },
                },
                images: {
                    required(images: File[] | FileList) {
                        return (<OfferFormRoute>(<any>this)).imageOrder.length > 0;
                    },
                    image(images: File[] | FileList) {
                        for (let image of Array.from(images)) {
                            if (!image.type.startsWith('image/'))
                                return false;
                        }

                        return true;
                    },
                    maxArray: withParams({
                        max: store.state.max_file_uploads
                    }, (images: File[] | FileList) => {
                        return images && images.length <= store.state.max_file_uploads;
                    }),
                    maxFile: withParams({
                        max: store.state.max_file_kb
                    }, (images: File[] | FileList) => {
                        const max = store.state.max_file_kb;
                        for (let image of Array.from(images)) {
                            if (image.size > max * 1024) {
                                return false;
                            }
                        }

                        return true;
                    })
                }
            }
        }
    })
    export default class OfferFormRoute extends mixins(route, routeFetch(fetchOffer, emptyOfferForm, false, afterFetch),
        routeGuard('auth', () => !!store.state.user), form) implements OfferFormRouteInterface {

        @Prop({type: Number})
        id: number | undefined;

        offer: Offer | null = null;
        form: FormData = {
            currency: store.state.currency_default
        };
        formModified: boolean = false;
        imageOrder: ImageOrderInstance[] = [];
        errors: { [index: string]: string | null } = {};
        valids: { [index: string]: boolean } = {};

        currencies: Currencies = {};
        priceCleave: any | null;
        resettingFileInput: boolean = false;

        touchPrice() {
            this.$v.form.price.$touch();
            this.$v.form.currency.$touch();
        }

        addExistingImages(forceNewArray: boolean = false) {
            let imageOrder: ImageOrderInstance[];

            if (forceNewArray)
                imageOrder = [];
            else
                imageOrder = this.imageOrder.filter(image => image.new);

            if (this.offer) {
                for (let [index, image] of this.offer.images.entries()) {
                    imageOrder.push({
                        src: image.urls.thumbnail ? image.urls.thumbnail : image.urls.original,
                        new: false,
                        id: image.id,
                        key: index.toString()
                    });
                }
            }

            this.imageOrder = imageOrder;
        }

        async resetImageOrder() {
            this.resettingFileInput = true;
            await this.$nextTick();
            this.resettingFileInput = false;

            this.addExistingImages(true);
        }

        @Watch('form')
        onFormModified() {
            this.formModified = true;
        }

        @Watch('offer')
        onOfferModified() {
            this.addExistingImages();
        }

        onPriceInput() {
            this.touchPrice();
            let price: string | number = parseFloat(this.priceCleave.getRawValue());

            if (isNaN(price) || !isFinite(price))
                price = '';

            this.form = {...this.form, price: price};
        }

        onFileInput(f: FileList) {
            this.form = {...this.form, images: f};

            let imageOrder: ImageOrderInstance[] = this.imageOrder.filter(image => !image.new);

            //add new images
            for (let [index, image] of Array.from(f).entries()) {
                const entry = {
                    src: undefined,
                    new: true,
                    id: index,
                    key: `new|${index}`
                };

                imageOrder.push(entry);

                getImageFileThumbnailDataURL(image)
                    .then(url => {
                        const imageOrder = this.imageOrder;
                        const i = imageOrder.indexOf(entry);

                        if (i >= 0) {
                            imageOrder[i].src = url;
                            this.imageOrder = imageOrder;
                        }
                    });
            }

            this.imageOrder = imageOrder;
        }

        removeExistingMessage(id: number) {
            const imageOrder = this.imageOrder;
            const index = imageOrder.findIndex(image => !image.new && image.id === id);

            if (index >= 0) {
                imageOrder.splice(index, 1);
                this.imageOrder = imageOrder;
            }
        }

        submit(asDraft = false) {
            const formData = new FormData(this.$refs.form as HTMLFormElement);
            formData.set('price', this.form.price ? this.form.price.toString() : '0');
            formData.set('status', asDraft ? '0' : '1');

            if (this.offer)
                formData.set('id', this.offer.id.toString());

            const imageOrder = Object.values(this.imageOrder).map(val => ({
                new: val.new,
                id: val.id
            }));

            formData.set('imageOrder', JSON.stringify(imageOrder));

            const onSubmitSuccess = (result: { id?: number }) => {
                this.form = {};

                if (result && result.id)
                    this.$router.replace({name: 'offer', params: {id: result.id.toString()}});
                else if (this.offer)
                    this.$router.replace({name: 'offer', params: {id: this.offer.id.toString()}});
                else
                    this.$router.replace({name: 'index'});
            };

            this.formModified = false;
            this.$submitForm(this.offer ? 'offer-edit' : 'offer-create', 'form', onSubmitSuccess, formData, asDraft);
        }

        setObjArg(objName: string, key: string, value: any) {
            const obj = (<any>this)[objName];
            (<any>this)[objName] = {...obj, [key]: value};
        }

        get title() {
            return this.$store.getters.trans(`interface.page.offer-${this.id ? 'edit' : 'create'}`);
        }

        get price() {
            if (typeof this.form.currency !== "string" || (this.form.price !== 0 && !this.form.price))
                return '';

            const price = typeof this.form.price === 'number' ? this.form.price : parseFloat(this.form.price);

            try {
                const val = new Intl.NumberFormat(this.$store.state.locale,
                    {style: 'currency', currency: this.form.currency}).format(price);

                return val.substr(0, 3) !== 'NaN' ? val : '';
            } catch (error) {
                return '';
            }
        }

        get choicesCurrencies(): ChoicesCurrencies[] {
            const currencies: ChoicesCurrencies[] = Object.keys(this.currencies).map((currency, index) => ({
                value: currency,
                label: currency,
                id: index
            }));

            currencies.push({
                value: 0,
                label: this.$store.getters.trans('interface.form.currency'),
                selected: true,
                placeholder: true,
            });

            return currencies;
        }

        get translations(): TranslationMessages {
            return {
                button: {
                    publish: this.$store.getters.trans('interface.button.publish'),
                    close: this.$store.getters.trans('interface.button.close'),
                    revert: this.$store.getters.trans('interface.button.revert-images'),
                },
                form: {
                    extended: {
                        name: this.$store.getters.trans('interface.form.extended.offer-name'),
                        description: this.$store.getters.trans('interface.form.extended.offer-description'),
                        images: this.$store.getters.trans('interface.form.extended.offer-images'),
                    },
                    name: this.$store.getters.trans('interface.form.offer-name'),
                    description: this.$store.getters.trans('interface.form.offer-description'),
                    images: this.$store.getters.trans('interface.form.offer-images'),
                    reorder: this.$store.getters.trans('interface.form.reorder-images'),
                    currency: this.$store.getters.trans('interface.form.currency'),
                    price: this.$store.getters.trans('interface.form.price'),
                }
            }
        }

        get priceValid() {
            return this.valids.price && this.valids.currency;
        }

        get priceError() {
            return this.errors.price || this.errors.currency;
        }

        async mounted() {
            this.priceCleave = new Cleave(this.$refs.price, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralIntegerScale: 30,
                numeralDecimalScale: 8,
                numeralPositiveOnly: true,
            });
            this.currencies = store.state.currencies;
        }

        beforeDestroy() {
            this.priceCleave.destroy();
        }

        beforeRouteLeave(to: Route, from: Route, next: (to?: RawLocation | false | ((vm: any) => any) | void) => void) {
            if (!this.formModified || window.confirm(this.$store.getters.trans('interface.confirm.form-leave'))) {
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

    .thumbnail-border {
        overflow: hidden;
        border: 1px solid $primary;
    }
</style>
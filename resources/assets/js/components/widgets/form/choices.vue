<template>
    <div ref="wrapper" :class="['form-control', elemClass]">
        <select ref="select" :name="name"></select>
    </div>
</template>

<script lang="ts">
    //@ts-ignore
    import Choices from 'choices.js';
    import {Component, Prop, Vue, Watch} from 'JS/components/class-component';

    @Component({
        name: "choices",
    })
    export default class ChoicesComponent extends Vue {
        @Prop({type: Array, default: () => []})
        items!: any[];

        @Prop({type: Object, default: () => ({})})
        options!: { [index: string]: any };

        @Prop({})
        value: any;

        @Prop({})
        elemClass: any;

        @Prop({type: String})
        name: string | undefined;

        choices: any = null;
        el: Element | undefined;

        @Watch('elemClass')
        async onElemClassChanged() {
            const wrapper = <Element> this.$refs.wrapper;
            const el = this.el;

            if (!el) return;

            const oldClass = wrapper.className.split(' ');
            await this.$nextTick();
            const newClass = wrapper.className.split(' ');

            el.className = [...el.className.split(' '), ...newClass]
                .filter((v, i, a) => a.indexOf(v) === i && (newClass.indexOf(v) >= 0 || oldClass.indexOf(v) < 0))
                .join(' ');
        }

        @Watch('items')
        onItemsChanged(val: any[]) {
            this.choices.setChoices(val, 'value', 'label', true);

            if (this.value !== this.choices.getValue(true)) {
                this.choices.setValueByChoice(this.value);
            }
        }

        @Watch('value')
        onValueChanged(val: any) {
            if (val !== this.choices.getValue(true))
                this.choices.setValueByChoice(val);
        }

        mounted() {
            const wrapper = <HTMLElement> this.$refs.wrapper;

            const options = this.options;
            const clss = wrapper.className;
            const choices = this.choices = new Choices(this.$refs.select, {
                loadingText: this.$store.getters.trans('interface.notice.loading'),
                noResultsText: this.$store.getters.trans('interface.choices.no-results'),
                noChoicesText: this.$store.getters.trans('interface.choices.no-choices'),
                itemSelectText: this.$store.getters.trans('interface.choices.select'),
                addItemText: (value: string) => this.$store.getters.trans('interface.choices.add', {value: value}),
                maxItemText: (maxItemCount: number) => this.$store.getters.trans('interface.choices.max', {max: maxItemCount}),
                callbackOnCreateTemplates(template: any) {
                    const classNames = this.config.classNames;
                    return {
                        containerOuter: (direction: string) => {
                            return template(`
            <div class="${classNames.containerOuter} ${clss}" data-type="${this.passedElement.type}" ${this.passedElement.type === 'select-one' ? 'tabindex="0"' : ''} aria-haspopup="true" aria-expanded="false" dir="${direction}"></div>
          `);
                        },
                    }
                },
                ...options
            });

            this.el = wrapper.children[0];
            if (wrapper.parentNode) {
                wrapper.parentNode.insertBefore(this.el, wrapper.nextSibling);
            }
            wrapper.style.display = 'none';

            const onChoice = (e: Event) => {
                //@ts-ignore
                this.$emit('input', e.detail.choice.value);
            };

            this.$onJS(choices.passedElement, 'choice', onChoice);
        }

        beforeDestroy() {
            this.choices.destroy();
        }
    }
</script>

<style lang="scss" type="text/scss">
    $choices-selector: 'choices';
    $choices-font-size-lg: 16px;
    $choices-font-size-md: .8rem;
    $choices-font-size-sm: 12px;
    $choices-guttering: 24px;
    $choices-border-radius: 2.5px;
    $choices-border-radius-item: 20px;
    $choices-bg-color: #fff;
    $choices-bg-color-disabled: #EAEAEA;
    $choices-bg-color-dropdown: #FFFFFF;
    $choices-text-color: #333333;
    $choices-keyline-color: #DDDDDD;
    $choices-primary-color: #00BCD4;
    $choices-disabled-color: #eaeaea;
    $choices-highlight-color: $choices-primary-color;
    $choices-button-icon-path: '~choices.js/assets/icons';
    $choices-button-dimension: 8px;
    $choices-button-offset: 8px;

    @import '~choices.js/assets/styles/scss/choices.scss';

    .form-control {
        &.choices {
            padding: 0;
            padding-right: 20px;

            .input-group & {
                flex-shrink: 0;
                flex-grow: 0;
                width: auto;

                @media (min-width: 640px) {
                    min-width: 200px;
                }
            }
        }

        .choices__inner {
            background: none;
            border: none;
            padding: 0.375rem 0.75rem !important;
            min-height: 38px;
            margin: -1px;
            min-width: 100px;
        }

        .choices__list--single {
            padding: 0;
            vertical-align: #{-1* $choices-font-size-md / 4};
        }

        .choices__list--dropdown {
            z-index: 1200;
        }

    }
</style>
<template>
    <div ref="wrapper" class="form-control">
        <select ref="select"></select>
    </div>
</template>

<script>
    import Choices from 'choices.js';
    import events from 'JS/components/mixins/events';

    export default {
        name: "choices",
        mixins: [events],
        props: {
            items: {
                type: Array,
                default: () => []
            },
            options: {
                type: Object,
                default: () => ({})
            },
            value: undefined
        },
        watch: {
            items(val) {
                this.choices.setChoices(val, 'value', 'label', true);
            },
            value(val) {
                if (val !== this.choices.getValue(true))
                    this.choices.setValueByChoice(val);
            }
        },
        data: () => ({
            choices: null
        }),
        mounted() {
            const wrapper = this.$refs.wrapper;

            const options = this.options;
            console.log(options);
            const clss = wrapper.className;
            const choices = this.choices = new Choices(this.$refs.select, {
                loadingText: 'Loading...',
                noResultsText: 'No results found',
                noChoicesText: 'No choices to choose from',
                itemSelectText: 'Press to select',
                addItemText: value => `Press Enter to add <b>"${value}"</b>`,
                maxItemText: maxItemCount => `Only ${maxItemCount} values can be added.`, //TODO translate
                callbackOnCreateTemplates(template) {
                    const classNames = this.config.classNames;
                    return {
                        containerOuter: (direction) => {
                            return template(`
            <div class="${classNames.containerOuter} ${clss}" data-type="${this.passedElement.type}" ${this.passedElement.type === 'select-one' ? 'tabindex="0"' : ''} aria-haspopup="true" aria-expanded="false" dir="${direction}"></div>
          `);
                        },
                        /*containerInner: () => {
                            return template(`
            <div class="${classNames.containerInner}"></div>
          `);
                        },*/
                        /*itemList: () => {
                            return template(`
            <div class="${classNames.list} ${this.passedElement.type === 'select-one' ? classNames.listSingle : classNames.listItems}"></div>
          `);
                        },*/
                        /*placeholder: (value) => {
                            return template(`
            <div class="${classNames.placeholder}">${value}</div>
          `);
                        },*/
                        /*item: (data) => {
                            if (this.config.removeItemButton) {
                                return template(`
              <div class="${classNames.item} ${data.highlighted ? classNames.highlightedState : ''} ${!data.disabled ? classNames.itemSelectable : ''}" data-item data-id="${data.id}" data-value="${data.value}" ${data.active ? 'aria-selected="true"' : ''} ${data.disabled ? 'aria-disabled="true"' : ''} data-deletable>
              ${data.label}<button class="${classNames.button}" data-button>Remove item</button>
              </div>
            `);
                            }
                            return template(`
          <div class="${classNames.item} ${data.highlighted ? classNames.highlightedState : classNames.itemSelectable}"  data-item data-id="${data.id}" data-value="${data.value}" ${data.active ? 'aria-selected="true"' : ''} ${data.disabled ? 'aria-disabled="true"' : ''}>
            ${data.label}
          </div>
          `);
                        },*/
                        /*choiceList: () => {
                            return template(`
            <div class="${classNames.list}" dir="ltr" role="listbox" ${this.passedElement.type !== 'select-one' ? 'aria-multiselectable="true"' : ''}></div>
          `);
                        },*/
                        /*choiceGroup: (data) => {
                            return template(`
            <div class="${classNames.group} ${data.disabled ? classNames.itemDisabled : ''}" data-group data-id="${data.id}" data-value="${data.value}" role="group" ${data.disabled ? 'aria-disabled="true"' : ''}>
              <div class="${classNames.groupHeading}">${data.value}</div>
            </div>
          `);
                        },*/
                        /*choice: (data) => {
                            return template(`
          <div class="${classNames.item} ${classNames.itemChoice} ${data.disabled ? classNames.itemDisabled : classNames.itemSelectable}" data-select-text="${this.config.itemSelectText}" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'} data-id="${data.id}" data-value="${data.value}" ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}>
              ${data.label}
            </div>
          `);
                        },*/
                        /*input: () => {
                            return template(`
          <input type="text" class="${classNames.input} ${classNames.inputCloned}" autocomplete="off" autocapitalize="off" spellcheck="false" role="textbox" aria-autocomplete="list">
          `);
                        },*/
                        /*dropdown: () => {
                            return template(`
            <div class="${classNames.list} ${classNames.listDropdown}" aria-expanded="false"></div>
          `);
                        },*/
                        /*notice: (label) => {
                            return template(`
            <div class="${classNames.item} ${classNames.itemChoice}">${label}</div>
          `);
                        },*/
                        /*option: (data) => {
                            return template(`
            <option value="${data.value}" selected>${data.label}</option>
          `);
                        },*/
                    }
                },
                ...options
            });

            wrapper.parentNode.insertBefore(wrapper.children[0], wrapper.nextSibling);
            wrapper.style.display = 'none';

            this.$onJS(choices.passedElement, 'choice', e => {
                this.$emit('input', e.detail.choice.value);
            });
        },
        beforeDestroy() {
            this.choices.destroy();
        }
    };
</script>

<style lang="scss" type="text/scss">
    $choices-selector: 'choices';
    $choices-font-size-lg: 16px;
    $choices-font-size-md: 14px;
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

    .choices.form-control {
        padding: 0;

        .input-group & {
            flex-shrink: 0;
            flex-grow: 0;
            width: auto;
            min-width: 200px;
        }
    }

    .choices__inner {
        background: none;
        border: none;
    }
</style>
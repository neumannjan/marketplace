<template>
    <div>
        <div :class="['custom-control custom-checkbox', {disabled: disabled}]">
            <input class="custom-control-input" :type="type" :value="value" :id="id" :name="name"
                   @change="$emit('input', $event.target.checked)" :disabled="disabled">
            <label class="custom-control-label" :for="id">
                <slot/>
            </label>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";

    let nextID = 0;

    export default Vue.extend({
        name: 'form-select',
        props: {
            disabled: Boolean,
            value: Boolean,
            name: String,
            type: {
                type: String,
                default: 'checkbox'
            }
        },
        data: (): {
            id: string | null
        } => ({
            id: null
        }),
        created() {
            this.id = 'select-' + nextID++;
        }
    });
</script>
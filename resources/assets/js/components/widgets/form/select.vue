<template>
    <div>
        <div :class="['form-check', {disabled: disabled}]">
            <label class="form-check-label">
                <input class="form-check-input" :type="type" :value="value"
                       @change="$emit('input', $event.target.checked)" :disabled="disabled">
                <slot></slot>
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            disabled: Boolean,
            value: Boolean,
            type: {
                type: String,
                default: 'checkbox'
            }
        }
    };
</script>
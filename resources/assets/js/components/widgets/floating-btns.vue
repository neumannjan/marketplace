<template>
    <transition-group
            name="tr"
            tag="div"
            class="d-flex flex-column-reverse">
        <button v-for="(button, index) in filteredButtons"
                ref="buttons"
                :key="button.id ? button.id : button.icon"
                :title="button.label()"
                @click="onClick(button, index)"
                :class="['btn btn-floating', `btn-floating-${index}`, button.class ? button.class : 'btn-dark']">
            <icon :name="button.icon"/>
            <transition name="badge">
                <span v-if="badges[button.id]"
                      :class="`badge badge-${button.badgeType ? button.badgeType : 'danger'}`">
                    <span>{{ badges[button.id] }}</span>
                </span>
            </transition>
        </button>
    </transition-group>
</template>

<script>
    export default {
        name: 'floating-btns',
        props: {
            buttons: {
                type: Array,
                required: true
            },
            badges: {
                type: Object,
                default: () => ({})
            }
        },
        watch: {
            filteredButtons(val, oldVal) {
                if (val !== oldVal) {
                    this.$emit('buttons', this.$refs.buttons);
                }
            }
        },
        computed: {
            filteredButtons() {
                //@ts-ignore
                return this.buttons.filter(button => !button.show || button.show(this.$route) !== false);
            }
        },
        methods: {
            /**
             * @param {object} button
             * @param {number} index
             */
            onClick(button, index) {
                //@ts-ignore
                this.$emit('click', button, this.$refs.buttons[index]);
            }
        },
        mounted() {
            this.$emit('buttons', this.$refs.buttons);
        }
    };

</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    $height: $btn-floating-margin + $btn-floating-size;
    $duration: 300ms;
    $ease-in-quad: cubic-bezier(0.55, 0.085, 0.68, 0.53);
    $ease-out-quad: cubic-bezier(0.25, 0.46, 0.45, 0.94);
    $ease-quad: cubic-bezier(0.455, 0.03, 0.515, 0.955);

    .btn-floating {
        position: absolute;
        right: 0;
        bottom: 0;
        transition: transform $duration $ease-quad;
    }

    .tr-enter, .tr-leave-to {
        transform: translateY($height) !important;
    }

    @for $i from 0 through 10 {
        .btn-floating-#{$i} {
            transform: translateY(-$height*$i);
            z-index: 1000 - $i;

            &.tr-leave-to {
                z-index: 900 - $i !important;
            }
        }
    }

    .tr-enter-active {
        transition: transform $duration $ease-out-quad;
        transition-delay: $duration/2;
    }

    .tr-leave-active {
        transition: transform $duration $ease-in-quad;
    }

    .badge {
        will-change: transform;
        transition: transform .3s ease;
    }

    .badge-enter, .badge-leave-to {
        transform: scale(0);
    }

    .badge-leave, .badge-enter-to {
        transform: scale(1);
    }
</style>

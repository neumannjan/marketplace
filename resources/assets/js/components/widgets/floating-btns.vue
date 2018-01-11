<template>
    <transition-group
            name="tr"
            tag="div"
            @enter="trEnter"
            class="fixed-bottom-right mb-4 flex flex-column">
        <button v-for="(button, index) in buttonsReversed" :key="index"
                :class="['btn btn-floating btn-dark', button.class]">
            <icon :name="button.icon" :label="button.label"/>
        </button>
    </transition-group>
</template>

<script>

    import Velocity from 'velocity-animate';

    export default {
        name: 'floating-btns',
        props: {
            buttons: {
                type: Array,
                required: true
            }
        },
        computed: {
            buttonsReversed() {
                return this.buttons.reverse();
            }
        },
        methods: {
            trEnter(el, done) {
                const rect = el.getBoundingClientRect();
                el.style.position = 'float';
                el.style.right = rect.right + 'px';
                el.style.bottom = (this.buttons.length - 1) * rect.height + 'px';

                Velocity(el, {
                    bottom: (this.buttons.length) * rect.height + 'px'
                }, {
                    duration: 500,
                    complete: () => {
                        el.style.position = 'relative';
                        done();
                    }
                });
            }
        }
    }

</script>

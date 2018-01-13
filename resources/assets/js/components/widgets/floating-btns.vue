<template>
    <transition-group
            name="tr"
            tag="div"
            v-bind:css="false"
            v-on:enter="enter"
            v-on:leave="leave"
            class="fixed-bottom-right d-flex flex-column-reverse">
        <button v-for="(button, index) in buttons" :key="button.id ? button.id : button.icon"
                :data-index="index"
                @click="$emit('click', button)"
                :class="['btn btn-floating', button.class ? button.class : 'btn-dark']">
            <icon :name="button.icon" :label="button.label"/>
        </button>
    </transition-group>
</template>

<script>
    import Velocity from 'velocity-animate';

    const ANIM_DURATION = 300;

    export default {
        name: 'floating-btns',
        props: {
            buttons: {
                type: Array,
                required: true
            },
        },
        data: () => ({
            prevLength: 0,
        }),
        methods: {
            anim(enter, el, done) {
                const style = getComputedStyle(el);
                const height = parseFloat(style.marginTop) + parseFloat(style.marginBottom) + parseFloat(style.height);

                const gone = -height;
                const shown = el.dataset.index * height;

                el.style.opacity = (enter ? 0 : 1);
                el.style.position = 'fixed';
                el.style.right = 0;
                el.style.bottom = (enter ? gone : shown) + 'px';
                el.style.zIndex = (enter ? 1000 : 800) - el.dataset.index;

                Velocity(el, 'stop');

                Velocity(
                    el,
                    {opacity: (enter ? 1 : 0), bottom: (enter ? shown : gone)},
                    {
                        duration: ANIM_DURATION,
                        easing: `ease${enter ? 'Out' : 'In'}Quad`,
                        complete: () => {
                            el.style.opacity = null;
                            el.style.position = null;
                            el.style.right = null;
                            el.style.bottom = null;
                            el.style.zIndex = null;
                            done();
                        },
                        delay: (enter ? ANIM_DURATION / 2 : 0)
                    }
                );
            },
            enter(el, done) {
                this.anim(true, el, done);
            },
            leave(el, done) {
                this.anim(false, el, done);
            }
        }
    }

</script>

<style>

</style>
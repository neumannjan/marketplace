<template>
    <floating-btns :buttons="buttons" @click="click"/>
</template>

<script>
    import FloatingBtns from './floating-btns';
    import events from 'JS/components/mixins/events';
    import router from 'JS/router';

    import 'vue-awesome/icons/plus';
    import 'vue-awesome/icons/comment';
    import 'vue-awesome/icons/bell';
    import 'vue-awesome/icons/angle-left';
    import 'vue-awesome/icons/angle-up';
    import 'vue-awesome/icons/search';

    const BTN_ADD = 'add';
    const BTN_CHAT = 'chat';
    const BTN_NOTIFICATIONS = 'notifications';
    const BTN_BACK = 'back';
    const BTN_UP = 'up';
    const BTN_SEARCH = 'search';

    //TODO labels
    const buttons = [
        {id: BTN_ADD, icon: 'plus', class: 'btn-primary'},
        {id: BTN_CHAT, icon: 'comment'},
        {id: BTN_NOTIFICATIONS, icon: 'bell'},
        {id: BTN_SEARCH, icon: 'search', class: 'btn-info'},
    ];

    const backButton = {id: BTN_BACK, icon: 'angle-left'};
    const upButton = {id: BTN_UP, icon: 'angle-up', class: 'btn-danger'};

    export default {
        name: 'main-floating-btns',
        components: {FloatingBtns},
        mixins: [events],
        data: () => ({
            buttons: [],
            scrollY: window.scrollY,
            backShown: false,
        }),
        watch: {
            '$route'(route) {
                this.scrollY = window.scrollY;
                this.backShown = !router.getCurrentRouteMainComponent().isTopLevelRoute;
                this.buttons = this.aButtons;
            }
        },
        computed: {
            aButtons() {
                return this.backShown ? [...buttons, backButton] : buttons;
            },
            bButtons() {
                return [upButton];
            }
        },
        methods: {
            click(button) {
                switch (button.id) {
                    case BTN_ADD:
                        break;
                    case BTN_CHAT:
                        break;
                    case BTN_NOTIFICATIONS:
                        break;
                    case BTN_BACK:
                        router.back();
                        break;
                    case BTN_UP:
                        window.scrollTo(window.scrollX, 0);
                        break;
                }
            }
        },
        mounted() {
            this.$onJS(window, 'scroll', () => {
                const scrollY = window.scrollY;

                if (scrollY <= 300 || scrollY > this.scrollY) {
                    this.buttons = this.aButtons;
                } else {
                    this.buttons = this.bButtons;
                }

                this.scrollY = scrollY;
            });

            this.buttons = this.aButtons;
        },
    };
</script>
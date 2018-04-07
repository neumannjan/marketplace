<template>
    <transition-group name="tr"
                      tag="div"
                      class="d-flex flex-column mr-3 mt-3">
        <alert v-for="notification of displayedNotifications"
               :key="notification.id"
               :type="notification.type ? notification.type : 'primary'"
               @close="closeNotification(notification.id)"
               :closable="!notification.persistent"
               @hover="hover => hoverNotification(notification, hover)"
               :class="['notification', {
               'notification-disappearing': hoverNotificationID !== notification.id && windowFocus && !notification.persistent}]">
            {{ notification.message }}
        </alert>
    </transition-group>
</template>

<script>
    import {mapState} from 'vuex';

    import Alert from "JS/components/widgets/alert.vue";
    import notifications from 'JS/notifications';

    export default {
        name: 'notifications',
        components: {Alert},
        data: () => ({
            /** @type {string | null} */
            hoverNotificationID: null,
            /** @type {function | null} */
            hoverNotification: null,
            windowFocus: true
        }),
        watch: {
            displayedNotifications(val, oldVal) {
                for (let notification of val) {
                    if (oldVal.indexOf(notification) === -1 && this.hoverNotification) {
                        this.hoverNotification(notification, false, false);
                    }
                }
            }
        },
        computed: {
            ...mapState({
                notifications: state => state.notifications
            }),
            displayedNotifications() {
                return Object.values(this.notifications)
                    .filter(n => n.read !== true);
            }
        },
        methods: {
            /**
             * @param {string} id
             */
            closeNotification(id) {
                notifications.hideNotification(id);

                if (this.hoverNotificationID === id) {
                    this.hoverNotificationID = null;
                }
            }
        },
        created() {
            // window focus
            this.windowFocus = document.hasFocus();
            this.$onJS(window, 'focus', () => this.windowFocus = true);
            this.$onJS(window, 'blur', () => this.windowFocus = false);

            // hoverNotification function
            const removeNotificationTimerIDs = {};

            /**
             * @param {{id: string, persistent?: boolean}} notification
             * @param {boolean} hover
             */
            this.hoverNotification = (notification, hover, setHover = true) => {
                if (setHover) {
                    this.hoverNotificationID = hover ? notification.id : null;
                }

                if (notification.persistent) {
                    return;
                }

                const timerID = removeNotificationTimerIDs[notification.id];

                if (timerID !== undefined) {
                    clearTimeout(timerID);
                }

                removeNotificationTimerIDs[notification.id] = setTimeout(() => {
                    if (this.windowFocus) {
                        this.closeNotification(notification.id);
                    }
                    removeNotificationTimerIDs[notification.id] = undefined;
                }, 15000);
            };

            for (let notification of this.displayedNotifications) {
                this.hoverNotification(notification, false, false);
            }
        }
    }
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    $opacity-max: 1;
    $opacity-max-hover: 1;

    .notification {
        width: $side-popup-width;
        word-wrap: break-word;

        opacity: $opacity-max;
        transition: opacity .5s ease;

        &:hover {
            opacity: $opacity-max-hover;
        }
    }

    @keyframes disappear {
        from {
            opacity: $opacity-max;
        }

        to {
            opacity: 0;
        }
    }

    .notification-disappearing:not(.tr-enter):not(.tr-enter-active):not(.tr-leave):not(.tr-leave-active) {
        animation: disappear 15000ms normal forwards ease-in;
    }

    .tr-enter {
        opacity: 0 !important;
    }

    .tr-enter-to {
        opacity: $opacity-max !important;
    }

    .tr-enter-active {
        transition-delay: .1s;
    }
</style>
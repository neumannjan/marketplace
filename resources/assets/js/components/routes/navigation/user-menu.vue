<template>
    <div role="group">
        <button v-for="button of buttons"
                :key="button.label"
                :title="button.label"
                @click="onClick(button)"
                :class="['btn btn-link', `text-${button.type}`]">
            <icon :name="button.icon"/>
        </button>
    </div>
</template>

<script lang="ts">
    import {Component, Prop, Vue} from 'JS/components/class-component';
    import {User, UserStatus} from 'JS/api/types';
    import {Location} from 'vue-router';
    import {events, Events} from 'JS/events';
    import api from 'JS/api';

    import 'vue-awesome/icons/comment';
    import 'vue-awesome/icons/ban';
    import {doAction} from 'JS/lib/helpers';
    import {FloatingButtonTypes} from 'JS/components/types';

    interface Button {
        label: string,
        icon: string,
        type: string,
        location?: Location,
        action?: () => void
    }

    @Component({
        name: 'user-menu'
    })
    export default class UserMenu extends Vue {
        @Prop({type: Object, required: true})
        value!: User;

        get isAdmin(): boolean {
            return this.$store.state.is_admin;
        }

        get isBanned(): boolean {
            return this.value.status === UserStatus.Banned;
        }

        get loggedUser(): User | null {
            return this.$store.state.user;
        }

        get isMe(): boolean {
            return !!this.loggedUser && this.loggedUser.username === this.value.username;
        }

        onClick(button: Button) {
            if (button.action) {
                button.action();
            } else if (button.location) {
                this.$router.push(button.location);
            }
        }

        get buttons(): Button[] {
            let loggedButtons: Button[] = [];
            let adminButtons: Button[] = [];

            if (!!this.loggedUser && !this.isBanned && !this.isMe) {
                loggedButtons = [
                    {
                        label: this.$store.getters.trans('interface.button.message'),
                        icon: 'comment',
                        type: 'muted',
                        action: () => {
                            events.dispatch(Events.RequestPopup, {
                                type: FloatingButtonTypes.Chat,
                                then: () => {
                                    events.dispatch(Events.RequestChat, this.value);
                                }
                            });
                        }
                    }
                ]
            }

            if (this.isAdmin && !this.isMe) {
                const replacements = {user: this.value.display_name};
                adminButtons = [
                    {
                        label: this.$store.getters.trans(`interface.button.${this.isBanned ? 'unban' : 'ban'}`),
                        icon: 'ban',
                        type: this.isBanned ? 'success' : 'danger',
                        action: () => {
                            doAction({
                                confirm: this.$store.getters.trans(`interface.confirm.${this.isBanned ? 'unban' : 'ban'}`, replacements),
                                beforeNotification: this.$store.getters.trans(`interface.notification.before.${this.isBanned ? 'unban' : 'ban'}`, replacements),
                                afterNotification: this.$store.getters.trans(`interface.notification.after.${this.isBanned ? 'unban' : 'ban'}`, replacements),
                            }, () => api.requestSingle<User>('user-admin', {
                                username: this.value.username,
                                status: this.isBanned ? UserStatus.Active : UserStatus.Banned
                            }).then(user => {
                                this.$emit('input', user);
                            }));
                        }
                    }
                ]
            }

            return [
                ...loggedButtons,
                ...adminButtons
            ]
        }
    }
</script>

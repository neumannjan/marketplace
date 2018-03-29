<template>
    <div role="group">
        <button v-for="button of buttons"
                :key="button.label"
                :title="button.label"
                @click="onClick(button)"
                :class="['btn btn-link', `text-${button.type}`]">
            <icon :name="button.icon" />
        </button>
    </div>
</template>

<script lang="ts">
    import { Vue, Component, Prop } from 'JS/components/class-component';
    import { User, UserStatus } from 'JS/api/types';
    import { Location } from 'vue-router';
    import { events, Events } from 'JS/events';
    import store from 'JS/store';
    import api from 'JS/api';

    import 'vue-awesome/icons/comment';
    import 'vue-awesome/icons/ban';
    import { doAction } from 'JS/lib/helpers';

    //TODO translate labels
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
            return store.state.is_admin;
        }

        get isBanned(): boolean {
            return this.value.status === UserStatus.Banned;
        }

        get loggedUser(): User | null {
            return store.state.user;
        }

        get isMe(): boolean {
            return !!this.loggedUser && this.loggedUser.username === this.value.username;
        }

        onClick(button: Button) {
            if(button.action) {
                button.action();
            } else if(button.location) {
                this.$router.push(button.location);
            }
        }

        get buttons(): Button[] {
            if(this.isMe) {
                return [];
            }

            let loggedButtons: Button[] = [];
            let adminButtons: Button[] = [];

            if(!!this.loggedUser) {
                loggedButtons = [
                    {
                        label: 'Contact',
                        icon: 'comment',
                        type: 'muted',
                        action: () => {
                            events.dispatch(Events.RequestPopup, {
                                type: 'chat',
                                then: () => {
                                    events.dispatch(Events.RequestChat, this.value);
                                }
                            });
                        }
                    }
                ]
            }

            if(this.isAdmin) {
                adminButtons = [
                    {
                        label: this.isBanned ? 'Unban' : 'Ban',
                        icon: 'ban',
                        type: this.isBanned ? 'success' : 'danger',
                        action: () => {
                            doAction({
                                confirm: `Are you sure you want to ${this.isBanned ? 'unban' : 'ban'} user ${this.value.username}?`,
                                beforeNotification: `${this.isBanned ? 'Unbanning' : 'Banning'} user ${this.value.username}`,
                                afterNotification: `${this.isBanned ? 'Unbanned' : 'Banned'} user ${this.value.username}`,
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

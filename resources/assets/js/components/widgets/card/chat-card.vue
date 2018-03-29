<template>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-row align-items-center">
                <template v-if="user">
                    <button class="btn btn-link btn-link-gray btn-wrapper btn-icon mr-1" @click="user = null">
                        <icon name="arrow-left" label="back"/>
                    </button>
                    <router-link :to="{name: 'user', params: {username: user.username}}"
                                 class="d-flex flex-row align-items-center no-decoration">
                        <profile-img :img="user.profile_image ? user.profile_image : {}" :img-size="imgSize"
                                     class="mr-2" :style="{margin: `-${imgSize/2}px 0`}"/>
                        <h1 class="h6 m-0 text-dark">{{ user.display_name }}</h1>
                    </router-link>
                </template>
                <template v-else>
                    <h1 class="h6 m-0">Chat</h1>
                </template>
                <button class="btn btn-link btn-link-gray btn-icon btn-wrapper ml-auto" @click="onClose">
                    <icon name="close" label="close"/>
                </button>
            </div>
        </div>

        <list-messages v-if="user" :user="user" :img-size="imgSize"
                        @sender="onMessageSender"
                        class="d-flex flex-grow flex-column overflow-hidden-y"
                        :indicator-size="indicatorSize"/>
        <list-conversations v-else @select="onSelectUser" :img-size="imgSize"/>
    </div>
</template>

<script lang="ts">
    import appEvents, {Events} from 'JS/events';
    import {Offer, User} from 'JS/api/types';
    import Vue from 'vue';
    import store from 'JS/store';
    import {ConversationMediatorInterface} from 'JS/api/messaging/typings';
    import {MessageSender} from 'JS/components/widgets/chat/types';

    import ListConversations from "JS/components/widgets/chat/list-conversations.vue";
    import ListMessages from "JS/components/widgets/chat/list-messages.vue";
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";

    import "vue-awesome/icons/arrow-left";
    import "vue-awesome/icons/close";

    export default Vue.extend({
        name: 'chat-card',
        components: {
            ListMessages,
            ListConversations,
            ProfileImg
        },
        props: {
            imgSize: {
                type: Number,
                default: 32
            },
            indicatorSize: {
                type: Number,
                default: 14
            }
        },
        data: (): {
            user: User | null,
            sender: MessageSender | null
        } => ({
            user: null,
            sender: null
        }),
        methods: {
            onClose() {
                this.$emit('close');
            },

            onSelectUser(user: User) {
                this.user = user;
            },

            onMessageSender(sender: MessageSender) {
                this.sender = sender;
            },

            async openChat(user: User): Promise<boolean> {
                if (!store.state.user || user.username === store.state.user.username) {
                    return false;
                }

                if (this.user) {
                    this.user = null;
                    await this.$nextTick();
                }

                this.user = user;

                await this.$nextTick();

                return true;
            }
        },
        watch: {
            user(user) {
                if(!user) {
                    this.sender = null;
                }
            }
        },
        created() {
            this.$onEventListener(appEvents, Events.RequestChat, (user: User) => {
                this.openChat(user);
            });

            this.$onEventListener(appEvents, Events.RequestBuy, (offer: Offer) => {
                this.openChat(offer.author).then(isOpen => {
                    if (isOpen && this.sender) {
                        this.sender('', {
                            offer: offer.id
                        }, {
                            offer: offer
                        });
                    }
                });
            });
        }
    });
</script>
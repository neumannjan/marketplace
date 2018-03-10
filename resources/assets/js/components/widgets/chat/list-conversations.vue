<template>
    <infinite-scroll as="ul" class="list-group list-group-flush"
                     :busy="busy" @request="request">
        <li v-for="conversation in conversations" :key="conversation.id" class="list-group-item px-2">
            <a href="#" @click.prevent="onSelect(conversation)" class="chat-user no-decoration">
                <profile-img :img="conversation.user.profile_image ? conversation.user.profile_image : {}"
                             :img-size="imgSize" class="mr-2"/>
                <span :is="conversation.read || isMine(conversation) ? 'span' : 'strong'"
                      class="d-flex flex-column chat-user-content">
                    <span class="text-truncate d-block">{{ conversation.user.display_name }}</span>
                    <chat-message-content as="small"
                                          :inline="true"
                                          :message="conversation"
                                          class="text-truncate d-block text-muted"/>
                </span>
            </a>
        </li>
        <li v-if="busy" class="list-group-item px-2 text-center">
            <icon name="spinner" label="Loading" pulse/>
        </li>
    </infinite-scroll>
</template>

<script>
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import events from 'JS/components/mixins/events';
    import api from "JS/api";
    import { Conversation, Message } from 'JS/api/types';
    import appEvents,{ Events } from 'JS/events';

    import "vue-awesome/icons/spinner";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll.vue";
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content.vue";

    export default {
        name: 'list-conversations',
        mixins: [events],
        components: {
            InfiniteScroll,
            ChatMessageContent,
            ProfileImg
        },
        props: {
            imgSize: {
                type: Number,
                default: 40
            }
        },
        data: () => ({
            conversations: {},
            busy: false,
            nextUrl: '/api/conversations'
        }),
        methods: {
            /**
             * @param {Conversation} conversation
             */
            onSelect(conversation) {
                this.$emit('select', conversation.user);
            },
            request() {
                if (this.nextUrl) {
                    this.busy = true;

                    api.requestByURL(this.nextUrl)
                        .then(result => {
                            this.busy = false;

                            const additional = {};

                            for (let conversation of result.data) {
                                additional[conversation.user.username] = conversation;
                            }

                            this.conversations = {
                                ...this.conversations,
                                ...additional
                            };
                            this.nextUrl = result.next_page_url;
                        })
                }
            },
            /**
             * @param {Conversation} conversation
             */
            isMine(conversation) {
                return this.$store.state.user && this.$store.state.user.username === conversation.from.username;
            }
        },
        created() {
            this.request();

            /**
             * @param {Conversation} message
             */
            const onOtherMessageSent = message => {
                message.user = message.from;
                this.$delete(this.conversations, message.user.username);
                this.conversations = {[message.user.username]: message, ...this.conversations};
            };

            this.$onEventListener(appEvents, Events.MessageSentOther, onOtherMessageSent);
        }
    }
</script>

<style scoped>
    .chat-user {
        position: relative;
        display: flex;
    }

    .chat-user-content {
        line-height: 100%;
        overflow: hidden;
    }
</style>
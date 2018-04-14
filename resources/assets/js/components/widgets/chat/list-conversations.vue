<template>
    <infinite-scroll as="ul" class="list-group list-group-flush overflow-scroll-y p-2"
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
        <li v-else-if="empty" class="list-group-item text-center h5 text-muted">
            {{ translations.empty }}
        </li>
    </infinite-scroll>
</template>

<script lang="ts">
    import ProfileImg from "JS/components/widgets/image/profile-img.vue";
    import api from "JS/api";
    import {Conversation, Message, User} from 'JS/api/types';
    import appEvents, {Events} from 'JS/events';
    import Vue from 'vue';

    import "vue-awesome/icons/spinner";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll.vue";
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content";
    import { TranslationMessages } from "lang.js";

    type Conversations = {
        [index: string]: Conversation
    }

    export default Vue.extend({
        name: 'list-conversations',
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
        data: (): {
            conversations: Conversations,
            busy: boolean,
            nextUrl: string | null
        } => ({
            conversations: {},
            busy: false,
            nextUrl: '/api/conversations'
        }),
        computed: {
            empty(): boolean {
                console.log(this.conversations);
                for(const c of Object.values(this.conversations)) {
                    return false;
                }

                return true;
            },
            translations(): TranslationMessages {
                return {
                    empty: this.$store.getters.trans('interface.notice.conversations-none'),
                }
            }
        },
        methods: {
            onSelect(conversation: Conversation) {
                this.$emit('select', conversation.user);
            },
            request() {
                if (this.nextUrl) {
                    this.busy = true;

                    api.requestByURL(this.nextUrl)
                        .then(result => {
                            this.busy = false;

                            const additional = {} as Conversations;

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
            isMine(conversation: Conversation) {
                return this.$store.state.user && this.$store.state.user.username === conversation.from.username;
            }
        },
        created() {
            this.request();

            this.$onEventListener(appEvents, Events.MessageSent, (message: Message & { user?: User }) => {
                if (!message.mine) {
                    message.user = message.from;
                    this.$delete(this.conversations, message.user.username);
                    this.conversations = {[message.user.username]: (<Conversation>message), ...this.conversations};
                }
            });
        }
    });
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
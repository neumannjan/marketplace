<template>
    <infinite-scroll as="ul" class="list-group list-group-flush"
                     :busy="busy" @request="request">
        <li v-for="conversation in conversations" :key="conversation.id" class="list-group-item px-2">
            <a href="#" @click.prevent="onSelect(conversation)" class="chat-user no-decoration">
                <profile-img :img="conversation.user.profile_image ? conversation.user.profile_image : {}"
                             :img-size="imgSize" class="mr-2"/>
                <div class="d-flex flex-column chat-user-content">
                    <span class="text-truncate d-block">{{ conversation.user.display_name }}</span>
                    <chat-message-content as="small"
                                          :inline="true"
                                          :message="conversation"
                                          class="text-truncate d-block text-muted"/>
                </div>
            </a>
        </li>
        <li v-if="busy" class="list-group-item px-2 text-center">
            <icon name="spinner" label="Loading" pulse/>
        </li>
    </infinite-scroll>
</template>

<script>
    import ProfileImg from "JS/components/widgets/image/profile-img";
    import api from "JS/api";
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content";

    import "vue-awesome/icons/spinner";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll";

    export default {
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
        data: () => ({
            conversations: [],
            busy: false,
            nextUrl: '/api/conversations'
        }),
        methods: {
            onSelect(conversation) {
                this.$emit('select', conversation.user);
            },
            request() {
                if (this.nextUrl) {
                    this.busy = true;

                    api.requestByURL(this.nextUrl)
                        .then(result => {
                            this.busy = false;
                            this.conversations = [...this.conversations, ...result.data];
                            this.nextUrl = result.next_page_url;
                        })
                }
            }
        },
        created() {
            this.request();
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
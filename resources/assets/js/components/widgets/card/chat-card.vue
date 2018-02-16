<template>
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-row align-items-center">
                <template v-if="user">
                    <button class="btn btn-link btn-link-gray btn-icon mr-1" @click="user = null">
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
                <button class="btn btn-link btn-link-gray btn-icon ml-auto" @click="onClose">
                    <icon name="close" label="close"/>
                </button>
            </div>
        </div>
        <div class="overflow-scroll-y p-2">
            <list-messages v-if="user" :user="user" :img-size="imgSize"
                           :indicator-size="indicatorSize"
                           v-model="addedMessages"/>
            <list-conversations v-else @select="onSelectUser" :img-size="imgSize"/>
        </div>

        <div v-if="user" class="p-1 mt-auto">
            <form @submit.prevent="sendMessage" class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="Type a message" v-model="message">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit">Send</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import echo from 'JS/echo';
    import helpers from 'JS/helpers';

    import debounce from 'lodash/debounce';

    import "vue-awesome/icons/arrow-left";
    import "vue-awesome/icons/close";
    import ListConversations from "JS/components/widgets/chat/list-conversations";
    import ListMessages from "JS/components/widgets/chat/list-messages";
    import ProfileImg from "JS/components/widgets/image/profile-img";

    export default {
        components: {
            ListMessages,
            ListConversations,
            ProfileImg
        },
        name: 'chat-card',
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
        data: () => ({
            user: null,
            message: '',
            addedMessages: {},
            notifyTyping: null
        }),
        watch: {
            message(val) {
                this.notifyTyping();
            }
        },
        methods: {
            sendMessage() {
                if (this.message) {
                    let uniqueId = null;
                    const content = this.message;
                    this.message = '';

                    // create a unique ID for awaited message
                    do {
                        uniqueId = (Math.random() + 1).toString(36).substr(2, 5);
                    } while (this.addedMessages[uniqueId] !== undefined);

                    // save to added messages
                    this.addedMessages = {
                        ...this.addedMessages,
                        [uniqueId]: {
                            content: content,
                            additional: [],
                            mine: true,
                            awaiting: true,
                        }
                    };
                }
            },
            onClose() {
                this.$emit('close');
            },
            onSelectUser(user) {
                this.addedMessages = {};
                this.user = user;
            },
            doNotifyTyping(typing) {
                if (!this.$store.state.user || !this.user) {
                    return;
                }

                const name = helpers.getConversationChannelName(this.$store.state.user.username, this.user.username);
                echo.channel('private', name)
                    .whisper('typing', {
                        typing: typing,
                        username: this.$store.state.user.username
                    });
            }
        },
        created() {
            const notifyTypingTrue = debounce(() => this.doNotifyTyping(this.message !== ''), 200, {
                leading: true,
                trailing: true
            });

            const notifyTypingFalse = debounce(() => this.doNotifyTyping(false), 10000);

            this.notifyTyping = () => {
                notifyTypingTrue();
                notifyTypingFalse();
            };
        }
    }
</script>
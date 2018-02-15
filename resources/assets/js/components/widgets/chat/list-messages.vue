<template>
    <infinite-scroll top :busy="busy" @request="request" @bottom="onBottom" v-model="scroll" class="d-flex flex-column">
        <div v-if="busy" class="text-center">
            <icon name="spinner" label="Loading" pulse/>
        </div>
        <div v-for="message in allMessages"
             :key="message.identifier ? message.identifier : message.id" class="mb-2">
            <div v-if="isMine(message)" class="chat-item-right d-flex flex-row-reverse align-items-end">
                <!-- TODO label -->
                <div class="chat-item-indicator mx-2">
                    <profile-img v-if="lastReadMessageID === message.id"
                                 :img="profileImage ? profileImage : {}"
                                 :img-size="indicatorSize"/>
                    <icon v-else-if="!message.read && message.received" name="check-circle" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <icon v-else-if="message.awaiting" name="circle-o" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <icon v-else-if="!message.read && !message.received" name="check-circle-o" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <div v-else :style="{width: `${indicatorSize}px`, height: '1px'}"></div>
                </div>
                <div class="chat-item-message card text-white bg-primary"
                     :style="{borderRadius: `${imgSize/2}px`}">
                    <chat-message-content class="m-0" :message="message"/>
                </div>
            </div>
            <div v-else class="d-flex flex-row align-items-end">
                <div class="chat-item-left d-flex flex-row">
                    <router-link :to="{name: 'user', params: {username: user.username}}" class="mx-2">
                        <profile-img :img="profileImage ? profileImage : {}" :img-size="imgSize"/>
                    </router-link>
                    <div class="chat-item-message card bg-light" :style="{borderRadius: `${imgSize/2}px`}">
                        <chat-message-content class="m-0" :message="message"/>
                    </div>
                </div>
                <div v-if="lastReadMessageID === message.id" class="chat-item-indicator ml-auto">
                    <profile-img :img="profileImage ? profileImage : {}"
                                 :img-size="indicatorSize"/>
                </div>
            </div>
        </div>
        <!-- Typing indicator -->
        <div v-if="typing" class="chat-item-left mb-2 d-flex flex-row align-items-end">
            <router-link :to="{name: 'user', params: {username: user.username}}" class="mx-2">
                <profile-img :img="profileImage ? profileImage : {}" :img-size="imgSize"/>
            </router-link>
            <div class="chat-item-message card bg-light" :style="{borderRadius: `${imgSize/2}px`}">
                <div class="chat-item-typing">
                    <div></div>
                </div>
            </div>
        </div>
    </infinite-scroll>
</template>

<script>
    import api from 'JS/api';
    import echo from 'JS/echo';
    import events from 'JS/components/mixins/events';
    import helpers from 'JS/helpers';

    import ProfileImg from 'JS/components/widgets/image/profile-img';
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll";
    // TODO: User chat notification and 'received' without 'read' on notification.

    export default {
        name: 'list-messages',
        mixins: [events],
        components: {
            InfiniteScroll,
            ChatMessageContent,
            ProfileImg
        },
        props: {
            user: {
                type: Object,
                required: true
            },
            imgSize: {
                type: Number,
                default: 32
            },
            indicatorSize: {
                type: Number,
                default: 14
            },
            value: {
                type: Object
            }
        },
        data: () => ({
            nextUrl: null,
            messages: [],
            messagesByKey: {},
            busy: false,
            atBottom: true,
            scroll: 0,
            typing: false,
        }),
        computed: {
            allMessages() {
                return [
                    ...this.messages,
                    ...Object.entries(this.value)
                        .filter(([identifier, message]) => this.messagesByKey[identifier] === undefined)
                        .map(([identifier, message]) => message)
                ];
            },
            profileImage() {
                return this.user.profile_image ? this.user.profile_image : null;
            },
            lastMessageID() {
                return this.allMessages.length > 0 ? this.allMessages[this.allMessages.length - 1].id : -1;
            },
            lastReadMessageID() {
                const messages = this.messages;

                // iterating backwards because the message we are looking for is likely to be close to the end
                for (let i = messages.length - 1; i >= 0; --i) {
                    const message = messages[i];

                    if (message.read) {
                        return message.id;
                    }
                }

                return -1;
            }
        },
        watch: {
            value(val, oldVal) {
                if (val !== oldVal) {
                    const newVal = Object.assign({}, val);

                    let changed = false;

                    for (let identifier of Object.keys(val)) {
                        if (this.messagesByKey[identifier] !== undefined) {
                            delete newVal[identifier];
                            changed = true;
                        }
                    }

                    if (changed) {
                        this.$emit('input', newVal);
                    }
                }
            }
        },
        methods: {
            isMine(message) {
                if (message.mine === true)
                    return true;

                if (!this.$store.state.user)
                    return false;

                return message.from.username === this.$store.state.user.username;
            },
            request() {
                if (this.nextUrl) {
                    this.busy = true;

                    api.requestByURL(this.nextUrl)
                        .then(result => {
                            this.busy = false;
                            this.addMessages(result.data, true);
                            this.nextUrl = result.next_page_url;
                        })
                }
            },
            addMessages(messages, toTop = false) {
                // filter out existing messages
                messages = messages
                    .filter(message => this.messagesByKey[message.id] === undefined)
                    .reverse();

                let mine = false;

                // add messages
                if (messages.length > 0) {
                    if (toTop) {
                        this.messages = [...messages, ...this.messages];
                    } else {
                        this.messages = [...this.messages, ...messages];
                    }

                    for (let message of messages) {
                        this.messagesByKey[message.id] = message;

                        if (message.identifier) {
                            this.messagesByKey[message.identifier] = message;
                        }

                        if (message.to === this.user.username) {
                            mine = true;
                        }
                    }
                }

                // scroll down
                this.scrollDown(mine);
            },
            async scrollDown(force = false) {
                if (!force && !this.atBottom) {
                    return;
                }

                if (this.scroll === 0) {
                    this.scroll = 1;
                    await this.$nextTick();
                }

                this.scroll = 0;
            },
            onBottom(atBottom) {
                this.atBottom = atBottom;
            },
            notifyReceived(messages, read = false) {
                if (!this.$store.state.user || !this.user) {
                    return;
                }

                let data = {
                    read: read
                };

                if (!Array.isArray(messages)) {
                    messages = [messages];
                }

                data.ids = messages.map(m => m.id);

                api.requestSingle('message-received-notify', data);

                const name = helpers.getConversationChannelName(this.user.username, this.$store.state.user.username);
                for (let message of messages) {
                    echo.channel('private', name)
                        .whisper('received', {
                            id: message.id,
                            read: read
                        });
                }
            },
            setReceived(messageID, read = false) {
                const messages = this.messages;

                // iterating backwards because the message we are looking for is likely to be close to the end
                for (let i = messages.length - 1; i >= 0; --i) {
                    const message = messages[i];

                    if (message.id === messageID) {
                        message.received = true;
                        message.read = read;
                        break;
                    }
                }

                this.messages = messages;
            },
            freshRequest(resetOuter = false) {
                if (!this.$store.state.user || !this.user) {
                    return;
                }

                this.nextUrl = `/api/messages?with=${this.user.username}`;
                this.messages = [];
                this.messagesByKey = {};

                if (resetOuter) {
                    this.$emit('input', {});
                }

                this.request();
            }
        },
        created() {
            if (!this.$store.state.user || !this.user) {
                return;
            }

            this.freshRequest();

            const name = helpers.getConversationChannelName(this.user.username, this.$store.state.user.username);

            this.$onEchoGlobal('reconnect', () => {
                this.freshRequest(true);
            });

            this.$onEcho('private', name, 'MessageSent', message => {
                this.addMessages([message]);
                this.typing = false;
                this.notifyReceived(message, true);
            });

            this.$onEcho('private', name, 'MessageReceived', message => {
                this.setReceived(message.id, message.read);
            });

            this.$onEchoWhisper('private', name, 'received', message => {
                this.setReceived(message.id, message.read);
            });

            this.$onEchoWhisper('private', name, 'typing', (data) => {
                if (data.username === this.user.username) {
                    this.typing = data.typing;
                    this.scrollDown();
                }
            });
        },

    }
</script>

<style scoped lang="scss" type="text/scss">
    @import "~CSS/includes";

    $chat-item-margin: map_get($spacers, 5);
    $image-margin: map_get($spacers, 2);

    .chat-item-left {
        margin-right: #{$chat-item-margin};
        margin-left: #{-1 * $image-margin};
    }

    .chat-item-right {
        margin-left: #{$chat-item-margin};
        margin-right: #{-1 * $image-margin};
    }

    .chat-item-message {
        padding: .25em .75em;
    }

    .chat-item-indicator {
        line-height: 0;
    }

    @keyframes typing {
        35% {
            transform: translateY(25%);
        }

        50% {
            transform: translateY(-50%);
        }

        65% {
            transform: translateY(25%);
        }
    }

    .chat-item-typing {
        display: inline-block;
        flex-direction: row;

        &:before, &:after {
            content: ' ';
        }

        & > *, &:before, &:after {
            width: 9px;
            height: 9px;
            margin: 0 .15em;
            border-radius: 50%;
            background: $gray-500;
            display: inline-block;
            will-change: transform;

            animation: typing 2s ease infinite;
            transform: translateY(25%);
        }

        & > * {
            animation-delay: .1s;
        }

        &:after {
            animation-delay: .2s;
        }
    }
</style>

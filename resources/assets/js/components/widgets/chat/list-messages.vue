<template>
    <infinite-scroll top :busy="busy" @request="request" @bottom="onBottom" v-model="scroll" class="d-flex flex-column">
        <div v-if="busy" class="text-center">
            <icon name="spinner" label="Loading" pulse/>
        </div>
        <div v-for="message in allMessages"
             :key="message.identifier ? message.identifier : message.id" class="mb-2">
            <div v-if="isMine(message)" class="chat-item-right d-flex flex-column align-items-end">
                <!-- TODO label -->
                <div class="d-flex flex-row align-items-end">
                    <div class="d-flex flex-row-reverse align-items-end ml-auto">
                        <div :class="['card text-white', message.error ? 'bg-danger' : 'bg-primary']"
                             :style="{borderRadius: `${imgSize/2}px`}">
                            <chat-message-content class="m-0" :message="message" :white="true" :img-size="imgSize"/>
                        </div>
                        <ul v-if="message.error" class="list-unstyled mb-0 mr-1 line-height-1">
                            <li>
                                <small><i><a href="#" @click.prevent="resendFailed(message.identifier)">Resend</a></i>
                                </small>
                            </li>
                            <li>
                                <small><i><a href="#" @click.prevent="removeFailed(message.identifier)">Remove</a></i>
                                </small>
                            </li>
                        </ul>
                    </div>
                    <div class="chat-item-indicator mx-2">
                        <profile-img v-if="lastReadMessageID === message.id"
                                     :img="profileImage ? profileImage : {}"
                                     :img-size="indicatorSize"/>
                        <icon v-else-if="message.error" name="times-circle" :scale="indicatorSize/16"
                              class="text-danger"/>
                        <icon v-else-if="!message.read && message.received" name="check-circle"
                              :scale="indicatorSize/16"
                              class="text-primary"/>
                        <icon v-else-if="message.awaiting" name="circle-o" :scale="indicatorSize/16"
                              class="text-primary"/>
                        <icon v-else-if="!message.read && !message.received" name="check-circle-o"
                              :scale="indicatorSize/16"
                              class="text-primary"/>
                        <div v-else :style="{width: `${indicatorSize}px`, height: '1px'}"></div>
                    </div>
                </div>
                <small v-if="message.error" class="text-danger"><i>Message send failed.</i></small>
            </div>
            <div v-else class="d-flex flex-row align-items-end">
                <div class="chat-item-left d-flex flex-row">
                    <router-link :to="{name: 'user', params: {username: user.username}}" class="mx-2">
                        <profile-img :img="profileImage ? profileImage : {}" :img-size="imgSize"/>
                    </router-link>
                    <div class="card bg-light" :style="{borderRadius: `${imgSize/2}px`}">
                        <chat-message-content class="m-0" :message="message" :img-size="imgSize"/>
                    </div>
                </div>
                <div v-if="lastReadMessageID === message.id" class="chat-item-indicator ml-auto">
                    <profile-img :img="profileImage ? profileImage : {}"
                                 :img-size="indicatorSize"/>
                </div>
            </div>
        </div>
        <!-- Typing indicator -->
        <div v-if="typing" class="chat-item-left mb-2 d-flex mr-auto flex-row-reverse align-items-end">
            <div class="card bg-light" :style="{borderRadius: `${imgSize/2}px`}">
                <div class="chat-item-typing">
                    <div></div>
                </div>
            </div>
            <router-link :to="{name: 'user', params: {username: user.username}}" class="mx-2">
                <profile-img :img="profileImage ? profileImage : {}" :img-size="imgSize"/>
            </router-link>
        </div>
    </infinite-scroll>
</template>

<script lang="ts">
    import api from 'JS/api';
    import echo from 'JS/echo';
    import helpers from 'JS/lib/helpers';
    import Vue from 'vue';

    import "vue-awesome/icons/spinner";
    import "vue-awesome/icons/check-circle-o";
    import "vue-awesome/icons/check-circle";
    import "vue-awesome/icons/circle-o";
    import "vue-awesome/icons/times-circle";

    import ProfileImg from 'JS/components/widgets/image/profile-img.vue';
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content.vue";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll.vue";
    import { Message, Image, MessageReceivedNotifyRequest, PaginatedResponse } from 'JS/api/types';
    import { ChannelType } from 'JS/lib/echo/channel';
    import { ConnectionManagerEvents } from 'JS/lib/echo';
    import { TypingEvent } from 'JS/echo/types';
    import appEvents,{ Events } from 'JS/events';

    // TODO: User chat notification and 'received' without 'read' on notification.

    type MessagesByKey = {
        [index: string]: Message
    }

    export default Vue.extend({
        name: 'list-messages',
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
        data: (): {
            nextUrl: string | null,
            messages: Message[],
            messagesByKey: MessagesByKey,
            busy: boolean,
            atBottom: boolean,
            scroll: number,
            typing: boolean
        } => ({
            nextUrl: null,
            messages: [],
            messagesByKey: {},
            busy: false,
            atBottom: true,
            scroll: 0,
            typing: false,
        }),
        computed: {
            allMessages(): Message[] {
                return [
                    ...this.messages,
                    ...<any>Object.entries(this.value)
                        .filter(([identifier, message]) => this.messagesByKey[identifier] === undefined)
                        .map(([identifier, message]) => message)
                ];
            },
            profileImage(): Image | null {
                return this.user.profile_image ? this.user.profile_image : null;
            },
            lastMessageID(): number {
                return this.allMessages.length > 0 ? this.allMessages[this.allMessages.length - 1].id : -1;
            },
            lastReadMessageID(): number {
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
            value(val: MessagesByKey, oldVal: MessagesByKey) {
                if (val !== oldVal) {
                    const newVal = Object.assign({}, val);

                    for (let [identifier, message] of Object.entries(val)) {
                        if (this.messagesByKey[identifier] !== undefined) {
                            // remove existing

                            delete newVal[identifier];
                            this.$emit('input', newVal);
                        } else if (message.awaiting) {
                            // send awaiting

                            api.requestSingle<Message>('message-send', {
                                to: this.user.username,
                                content: message.content,
                                additional: message.additional,
                                identifier: identifier
                            }).then(message => {
                                // add to messages
                                this.addMessages([message]);

                                // remove from value
                                delete newVal[identifier];
                                this.$emit('input', newVal);
                            }).catch(reason => {
                                // set as error
                                newVal[identifier].awaiting = false;
                                newVal[identifier].error = true;
                                newVal[identifier].identifier = identifier;
                                this.$emit('input', newVal);
                                this.scrollDown(true);
                            });
                        }
                    }
                }
            }
        },
        methods: {
            isMine(message: Message) {
                if (message.mine === true)
                    return true;

                if (!this.$store.state.user)
                    return false;

                return message.from.username === this.$store.state.user.username;
            },
            request() {
                if (this.nextUrl) {
                    this.busy = true;

                    api.requestByURL<PaginatedResponse<Message[]>>(this.nextUrl)
                        .then(result => {
                            this.busy = false;
                            this.addMessages(result.data, true);
                            this.nextUrl = result.next_page_url;
                        })
                }
            },
            resendFailed(identifier: string) {
                const msg = this.value[identifier];
                msg.awaiting = true;
                msg.error = false;

                this.$emit('input', {
                    ...this.value,
                    [identifier]: msg
                });
            },
            removeFailed(identifier: string) {
                const newVal = Object.assign({}, this.value);
                delete newVal[identifier];
                this.$emit('input', newVal);
            },
            addMessages(messages: Message[], toTop = false) {
                // filter out existing messages
                messages = messages
                    .filter(message => this.messagesByKey[message.id] === undefined
                        && (!message.identifier || this.messagesByKey[message.identifier] === undefined))
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
            onBottom(atBottom: boolean) {
                this.atBottom = atBottom;
            },
            notifyReceived(messages: Message[] | Message, read = false) {
                if (!this.$store.state.user || !this.user) {
                    return;
                }

                let data: MessageReceivedNotifyRequest = {
                    read: read,
                    ids: []
                };

                if (!Array.isArray(messages)) {
                    messages = [messages];
                }

                data.ids = messages.map(m => m.id);

                api.requestSingle('message-received-notify', data);

                const name = helpers.getConversationChannelName(this.user.username, this.$store.state.user.username);
                for (let message of messages) {
                    echo.channel(ChannelType.Private, name)
                        .whisper('received', {
                            id: message.id,
                            read: read
                        });
                }
            },
            setReceived(messageID: number, read = false) {
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
            freshRequest() {
                if (!this.$store.state.user || !this.user) {
                    return;
                }

                this.nextUrl = `/api/messages?with=${this.user.username}`;
                this.messages = [];
                this.messagesByKey = {};

                this.request();
            }
        },
        created() {
            if (!this.$store.state.user || !this.user) {
                return;
            }

            this.freshRequest();

            const name = helpers.getConversationChannelName(this.user.username, this.$store.state.user.username);

            this.$onEventListener(echo, ConnectionManagerEvents.Reconnect, this.freshRequest);

            this.$onEventListener(appEvents, Events.MessageSent, (message: Message) => {
                this.addMessages([message]);
                if(!message.mine) {
                    this.typing = false;
                    this.notifyReceived(message, true);
                }
            });

            const onMessageReceived = (message: Message) => {
                this.setReceived(message.id, message.read);
            }

            this.$onEcho(ChannelType.Private, name, 'MessageReceived', onMessageReceived);
            this.$onEchoWhisper(ChannelType.Private, name, 'received', onMessageReceived);

            this.$onEchoWhisper(ChannelType.Private, name, 'typing', (data: TypingEvent) => {
                if (data.username === this.user.username) {
                    this.typing = data.typing;
                    this.scrollDown();
                }
            });
        },

    });
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

    .d-flex {
        min-width: 0;
    }

    .flex-row {
        width: 100%;
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
        padding: .25em .75em;

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

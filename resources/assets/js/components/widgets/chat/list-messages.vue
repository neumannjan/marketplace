<template>
    <div>
        <infinite-scroll top :busy="busy" @request="request" @bottom="onBottom" v-model="scroll"
                         class="flex-grow d-flex flex-column overflow-scroll-y p-2">
            <div v-if="busy" class="text-center">
                <icon name="spinner" :label="translations.loading" pulse/>
            </div>
            <div v-for="message in allMessages"
                 :key="message.identifier ? message.identifier : message.id" class="mb-2">
                <div v-if="message.mine" class="chat-item-right d-flex flex-column align-items-end">
                    <div class="d-flex flex-row align-items-end">
                        <div class="d-flex flex-row-reverse align-items-end ml-auto">
                            <div :class="['card text-white', message.error ? 'bg-danger' : 'bg-primary']"
                                 :style="{borderRadius: `${imgSize/2}px`}">
                                <chat-message-content class="m-0" :message="message" :white="true" :img-size="imgSize"/>
                            </div>
                            <ul v-if="message.error" class="list-unstyled mb-0 mr-1 line-height-1">
                                <li>
                                    <small>
                                        <i><a href="#" @click.prevent="resendFailed(message.identifier)">{{
                                            translations.resend }}</a></i>
                                    </small>
                                </li>
                                <li>
                                    <small>
                                        <i><a href="#" @click.prevent="removeFailed(message.identifier)">{{
                                            translations.remove }}</a></i>
                                    </small>
                                </li>
                            </ul>
                        </div>
                        <div class="chat-item-indicator mx-2">
                            <profile-img v-if="lastReadMessageID === message.id"
                                         :img="profileImage ? profileImage : {}"
                                         :alt="translations.status.read"
                                         :img-size="indicatorSize"/>
                            <icon v-else-if="message.error" name="times-circle" :scale="indicatorSize/16"
                                  :label="translations.status.error"
                                  class="text-danger"/>
                            <icon v-else-if="!message.read && message.received" name="check-circle"
                                  :label="translations.status.received"
                                  :scale="indicatorSize/16"
                                  class="text-primary"/>
                            <icon v-else-if="message.awaiting" name="circle-o" :scale="indicatorSize/16"
                                  :label="translations.status.awaiting"
                                  class="text-primary"/>
                            <icon v-else-if="!message.read && !message.received" name="check-circle-o"
                                  :label="translations.status.sent"
                                  :scale="indicatorSize/16"
                                  class="text-primary"/>
                            <div v-else :style="{width: `${indicatorSize}px`, height: '1px'}"></div>
                        </div>
                    </div>
                    <small v-if="message.error" class="text-danger"><i>{{ translations.failed }}</i></small>
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
            <div v-if="typing" class="mb-2 mr-auto">
                <div class="chat-item-left d-flex flex-row-reverse align-items-end"
                     :aria-label="translations.status.typing">
                    <div class="card bg-light" :style="{borderRadius: `${imgSize/2}px`}">
                        <div class="chat-item-typing">
                            <div></div>
                        </div>
                    </div>
                    <router-link :to="{name: 'user', params: {username: user.username}}" class="mx-2">
                        <profile-img :img="profileImage ? profileImage : {}" :img-size="imgSize"/>
                    </router-link>
                </div>
            </div>
        </infinite-scroll>
        <div class="p-1">
            <form @submit.prevent="sendInputMessage" class="input-group input-group-sm">
                <input type="text" class="form-control" :placeholder="translations.typemsg" v-model="message" v-focus ref="input">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="submit">{{ translations.send }}</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script lang="ts">
    import ProfileImg from 'JS/components/widgets/image/profile-img.vue';
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll.vue";

    import "vue-awesome/icons/spinner";
    import "vue-awesome/icons/check-circle-o";
    import "vue-awesome/icons/check-circle";
    import "vue-awesome/icons/circle-o";
    import "vue-awesome/icons/times-circle";
    import messaging from 'JS/api/messaging';
    import debounce from 'lodash/debounce';
    import {
        ContinuousResponse,
        Image,
        MessageAdditional,
        MessageReceivedNotifyRequest,
        PaginatedResponse,
        User
    } from 'JS/api/types';
    import {ChannelType} from 'JS/lib/echo/channel';
    import {ConnectionManagerEvents} from 'JS/lib/echo';
    import {TypingEvent} from 'JS/echo/types';
    import {Events} from 'JS/events';
    import {Component, Prop, Vue, Watch} from 'JS/components/class-component';
    import {NormalizedMessage} from 'JS/api/messaging/typings';
    import {MessageSender} from 'JS/components/widgets/chat/types';
    import {ConversationEvents, ConversationMediator} from 'JS/api/messaging/conversation';
    import {TranslationMessages} from 'lang.js';

    interface LocalMessage {
        identifier: string,
        content: string,
        additional: MessageAdditional,
        additionalPrivate: MessageAdditional,
        mine: boolean,
        awaiting: boolean,
        error?: boolean
    }

    type Message = LocalMessage | NormalizedMessage;

    type MessagesByKey = {
        [index: string]: Message
    }

    @Component({
        name: 'list-messages',
        components: {
            InfiniteScroll,
            ChatMessageContent,
            ProfileImg
        }
    })
    export default class ListMessages extends Vue {
        @Prop({type: Object, required: true})
        user!: User;

        @Prop({type: Number, default: 32})
        imgSize: number = 32;

        @Prop({type: Number, default: 14})
        indicatorSize: number = 14;

        cm: ConversationMediator | null = null;

        nextFetch: (() => Promise<ContinuousResponse<NormalizedMessage[]>>) | null = null;

        message: string = '';

        addedMessages: { [index: string]: LocalMessage } = {};

        notifyTyping: (() => void) | null = null;

        messages: NormalizedMessage[] = [];

        messagesByKey: MessagesByKey = {};

        busy: boolean = false;

        atBottom: boolean = true;

        scroll: number = 0;

        typing: boolean = false;

        get translations(): TranslationMessages {
            return {
                typemsg: this.$store.getters.trans('interface.hint.type-message'),
                loading: this.$store.getters.trans('interface.notice.loading'),
                resend: this.$store.getters.trans('interface.button.resend'),
                send: this.$store.getters.trans('interface.button.send'),
                remove: this.$store.getters.trans('interface.button.remove'),
                failed: this.$store.getters.trans('interface.notice.message-failed'),
                status: {
                    error: this.$store.getters.trans('interface.message.error'),
                    read: this.$store.getters.trans('interface.message.read'),
                    awaiting: this.$store.getters.trans('interface.message.awaiting'),
                    received: this.$store.getters.trans('interface.message.received'),
                    sent: this.$store.getters.trans('interface.message.sent'),
                    typing: this.$store.getters.trans('interface.message.typing'),
                }
            }
        }

        get allMessages(): Message[] {
            return [
                ...this.messages,
                ...Object.entries(this.addedMessages)
                    .filter(([identifier, message]) => this.messagesByKey[identifier] === undefined)
                    .map(([identifier, message]) => message)
            ];
        }

        get profileImage(): Image | null {
            return this.user.profile_image ? this.user.profile_image : null;
        }

        get lastReadMessageID(): number {
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

        @Watch('message')
        onMessageChange() {
            if (this.notifyTyping) {
                this.notifyTyping();
            }
        }

        created() {
            if (!this.$store.state.user) {
                return;
            }

            this.cm = messaging.joinConversation(this.user);

            const messageSender: MessageSender = this.sendMessage;

            this.$emit('sender', this.sendMessage);

            this.freshRequest();

            this.cm.on(ConversationEvents.Reconnect, this.freshRequest);

            this.cm.on(ConversationEvents.Message, (message) => {
                this.addMessages(message);

                if (!message.mine) {
                    this.typing = false;
                }
            });

            this.cm.on(ConversationEvents.Received, payload => {
                this.setReceived(payload.id, payload.read);
            });

            this.cm.on(ConversationEvents.Typing, typing => {
                this.typing = typing;
                this.scrollDown();
            });

            //
            const notifyTypingTrue = debounce(() => {
                if (this.cm) {
                    this.cm.sendTyping(this.message !== '');
                }
            }, 200, {
                leading: true,
                trailing: true
            });

            const notifyTypingFalse = debounce(() => {
                if (this.cm) {
                    this.cm.sendTyping(false);
                }
            }, 10000);

            this.notifyTyping = () => {
                notifyTypingTrue();
                notifyTypingFalse();
            };
        }

        freshRequest() {
            if (!this.$store.state.user || !this.cm) {
                return;
            }

            this.nextFetch = this.cm.fetchMessages();
            this.messages = [];
            this.messagesByKey = {};

            this.request();
        }

        async request() {
            if (this.nextFetch) {
                this.busy = true;

                const result = await this.nextFetch();

                this.busy = false;
                this.addMessages(result.data, true);
                this.nextFetch = result.fetchMore;
            }
        }

        destroyed() {
            if (this.cm) {
                this.cm.dispose();
            }
        }

        sendInputMessage() {
            (<HTMLInputElement>this.$refs.input).focus();
            if (this.message) {
                this.sendMessage(this.message);
                this.message = '';
            }
        }

        sendMessage(content: string, additional: MessageAdditional = {}, additionalPrivate: MessageAdditional = {}) {
            if (this.cm) {
                const intermediate = this.cm.sendMessage(content, additional);

                const message: LocalMessage = {
                    identifier: intermediate.identifier,
                    content: content,
                    additional: additional,
                    additionalPrivate: additionalPrivate,
                    mine: true,
                    awaiting: true
                };

                // save to added messages
                this.addedMessages = {
                    ...this.addedMessages,
                    [intermediate.identifier]: message
                };

                this.$nextTick(() => {
                    this.scrollDown(true);
                });

                intermediate.promise
                    .then(message => {
                        Vue.delete(this.addedMessages, intermediate.identifier);
                        this.$nextTick(() => this.addMessages(message));
                    })
                    .catch(reason => {
                        message.awaiting = false;
                        message.error = true;
                        Vue.set(this.addedMessages, intermediate.identifier, message);
                        this.scrollDown(true);
                    });
            }
        }

        resendFailed(identifier: string) {
            const msg = this.addedMessages[identifier];
            msg.awaiting = true;
            msg.error = false;

            Vue.set(this.addedMessages, identifier, msg);
        }

        removeFailed(identifier: string) {
            Vue.delete(this.addedMessages, identifier);
        }

        addMessages(messages: NormalizedMessage[] | NormalizedMessage, toTop = false) {
            if (!Array.isArray(messages))
                messages = [messages];

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

                    if (message.mine) {
                        mine = true;
                    }
                }
            }

            // scroll down
            this.scrollDown(mine);
        }

        async scrollDown(force = false) {
            if (!force && !this.atBottom) {
                return;
            }

            if (this.scroll === 0) {
                this.scroll = 1;
                await this.$nextTick();
            }

            this.scroll = 0;
        }

        onBottom(atBottom: boolean) {
            this.atBottom = atBottom;
        }

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
        }
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

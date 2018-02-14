<template>
    <infinite-scroll top :busy="busy" @request="request" @bottom="onBottom" v-model="scroll" class="d-flex flex-column">
        <div v-if="busy" class="text-center">
            <icon name="spinner" label="Loading" pulse/>
        </div>
        <template v-for="message in allMessages">
            <div v-if="isMine(message)" class="chat-item-right mb-2 d-flex flex-row-reverse align-items-end">
                <!-- TODO label -->
                <div class="chat-item-indicator mx-2">
                    <icon v-if="false" name="check-circle" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <icon v-else-if="false" name="check-circle-o" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <icon v-else-if="message.awaiting" name="circle-o" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <profile-img v-else-if="false" :img="profileImage ? profileImage : {}"
                                 :img-size="indicatorSize"/>
                    <div v-else :style="{width: `${indicatorSize}px`, height: '1px'}"></div>
                </div>
                <div class="chat-item-message card text-white bg-primary"
                     :style="{borderRadius: `${imgSize/2}px`}">
                    <chat-message-content class="m-0" :message="message"/>
                </div>
            </div>
            <div v-else class="chat-item-left mb-2 d-flex flex-row align-items-end">
                <router-link :to="{name: 'user', params: {username: user.username}}" class="mx-2">
                    <profile-img :img="profileImage ? profileImage : {}" :img-size="imgSize"/>
                </router-link>
                <div class="chat-item-message card bg-light" :style="{borderRadius: `${imgSize/2}px`}">
                    <chat-message-content class="m-0" :message="message"/>
                </div>
            </div>
        </template>
        <!-- Typing indicator -->
        <div class="chat-item-left mb-2 d-flex flex-row align-items-end">
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
    import events from 'JS/components/mixins/events';

    import ProfileImg from 'JS/components/widgets/image/profile-img';
    import ChatMessageContent from "JS/components/widgets/chat/chat-message-content";
    import InfiniteScroll from "JS/components/widgets/infinite-scroll";

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
            scroll: 0
        }),
        computed: {
            allMessages() {
                return [
                    ...this.messages.slice(0).reverse(),
                    ...Object.entries(this.value)
                        .filter(([identifier, message]) => this.messagesByKey[identifier] === undefined)
                        .map(([identifier, message]) => message)
                ];
            },
            profileImage() {
                return this.user.profile_image ? this.user.profile_image : null;
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
                messages = messages.filter(message => this.messagesByKey[message.id] === undefined);

                // add messages
                if (messages.length > 0) {
                    if (toTop) {
                        this.messages = [...this.messages, ...messages];
                    } else {
                        this.messages = [...messages, ...this.messages];
                    }

                    for (let message of messages) {
                        this.messagesByKey[message.id] = message;

                        if (message.identifier) {
                            this.messagesByKey[message.identifier] = message;
                        }
                    }

                    // scroll down
                    this.scroll = 1;
                    this.$nextTick(() => {
                        this.scroll = 0;
                    });
                }
            },
            onBottom(atBottom) {
                this.atBottom = atBottom;
            }
        },
        created() {
            if (!this.$store.state.user || !this.user) {
                return;
            }

            this.nextUrl = `/api/messages?with=${this.user.username}`;
            this.request();

            function getChannelName(username1, username2) {
                const name = 'conversation';
                if (username1 <= username2)
                    return `${name}.${username1}.${username2}`;
                else
                    return `${name}.${username2}.${username1}`;
            }

            const name = getChannelName(this.user.username, this.$store.state.user.username);

            this.$onEcho('private', name, 'MessageSent', message => {
                this.addMessages([message]);
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
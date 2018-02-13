<template>
    <infinite-scroll top :busy="busy" @request="request" @bottom="onBottom" v-model="scroll" class="d-flex flex-column">
        <div v-if="busy" class="text-center">
            <icon name="spinner" label="Loading" pulse/>
        </div>
        <template v-for="message in messagesReverse">
            <div v-if="message.mine" class="chat-item-right mb-2 d-flex flex-row-reverse align-items-end">
                <!-- TODO label -->
                <div class="chat-item-indicator mx-2">
                    <icon v-if="message.id % 7 === 0" name="check-circle" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <icon v-else-if="message.id % 5 === 0" name="check-circle-o" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <icon v-else-if="message.id % 4 === 0" name="circle-o" :scale="indicatorSize/16"
                          class="text-primary"/>
                    <profile-img v-else-if="message.id % 2 === 0" :img="profileImage ? profileImage : {}"
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
            postedMessages: {
                type: Array,
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
            messagesReverse() {
                return this.messages.slice(0).reverse();
            },
            profileImage() {
                return this.user.profile_image ? this.user.profile_image : null;
            }
        },
        watch: {
            postedMessages(val, oldVal) {
                if (val !== oldVal) {
                    this.addMessages(val);
                }
            }
        },
        methods: {
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
                messages = messages.filter(message => this.messagesByKey[message.id] === undefined);

                if (messages.length > 0) {
                    if (toTop) {
                        this.messages = [...this.messages, ...messages];
                    } else {
                        this.messages = [...messages, ...this.messages];
                    }

                    for (let message of messages) {
                        this.messagesByKey[message.id] = message;
                    }

                    this.scroll = 0;
                }
            },
            onBottom(atBottom) {
                this.atBottom = atBottom;
            }
        },
        created() {
            this.nextUrl = `/api/messages?with=${this.user.username}`;
            this.request();

            this.$onEcho('MessageSent', message => {
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
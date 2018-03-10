<template>
    <p :is="as" class="chat-message">
        <template v-if="!inline">
            <placeholder-img v-if="!inline && imgSrc !== null && imgSrc !== undefined"
                             img-style="width: 100%"
                             placeholder-style="height: 80px"
                             placeholder-class="w-100"
                             class="chat-message-image"
                             :style="{borderTopLeftRadius: `${imgSize/2}px`, borderTopRightRadius: `${imgSize/2}px`}"
                             :src="imgSrc"/>
            <router-link v-if="route" :to="route" v-html="content"
                         :class="[white ? 'text-white' : 'text-dark', 'chat-message-content']"/>
            <span v-else v-html="content" class="chat-message-content"></span>
        </template>
        <span v-else v-html="content"></span>
    </p>
</template>

<script>
    import api from 'JS/api';
    import PlaceholderImg from "JS/components/widgets/image/placeholder-img.vue";
    import { Location } from 'vue-router/types/router';

    const TYPE_REGULAR = 'regular';
    const TYPE_OFFER = 'offer';

    const SURROGATE_PAIR_REGEXP = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
    // Match everything outside of normal chars and " (quote character)
    const NON_ALPHANUMERIC_REGEXP = /([^#-~ |!])/g;

    export default {
        components: {PlaceholderImg},
        name: "chat-message-content",
        props: {
            message: {
                type: Object,
                required: true
            },
            as: {
                default: 'p'
            },
            inline: {
                type: Boolean,
                default: false
            },
            white: {
                type: Boolean
            },
            imgSize: {
                type: Number,
                default: 32
            }
        },
        data: () => ({
            content: '',
            /** @type {string | null} */
            imgSrc: null,
            /** @type {Location | null} */
            route: null
        }),
        watch: {
            message(val, oldVal) {
                if (val !== oldVal) {
                    this.setContent();
                }
            }
        },
        methods: {
            setContent() {
                switch (this.type) {
                    case TYPE_OFFER:
                        this.setAsOffer();
                        break;
                    case TYPE_REGULAR:
                    default:
                        if (this.inline) {
                            this.content = this.escapeString(`${this.user.display_name}: ${this.message.content}`);
                        } else {
                            this.content = this.escapeString(this.message.content);
                        }
                        this.imgSrc = null;
                        this.route = null;
                        break;
                }
            },
            async setAsOffer() {
                const afterContent = `<i>User ${this.escapeString(this.user.display_name)} wants to buy something!</i>`;

                if (this.inline) {
                    this.content = afterContent;
                    this.imgSrc = null;
                    this.route = null;
                    return;
                }

                let offer;
                if (!this.message.additionalPrivate || !this.message.additionalPrivate.offer) {
                    if (typeof this.message.additional.offer === 'object') {
                        offer = this.message.additional.offer;
                    } else {
                        this.content = afterContent;
                        this.imgSrc = '';
                        this.route = {query: {offer: this.message.additional.offer}};

                        offer = await api.requestSingle('offer', {
                            scope: this.$store.getters.scope.offer,
                            id: this.message.additional.offer
                        });
                    }
                } else {
                    offer = this.message.additionalPrivate.offer;
                }

                this.content = `<h2 class="h5">${this.escapeString(offer.name)}</h2>${afterContent}`;
                this.imgSrc = offer.images.length > 0 ? offer.images[0].urls.original : null;
                this.route = {query: {offer: offer.id}};
            },
            /**
             * @param {string} string
             */
            escapeString(string) {
                if (!string)
                    return '';

                // code taken from
                // https://github.com/angular/angular.js/blob/8d6ac5f3178cb6ead6b3b7526c50cd1c07112097/src/ngSanitize/sanitize.js
                return string
                    .replace(/&/g, '&amp;')
                    //@ts-ignore
                    .replace(SURROGATE_PAIR_REGEXP, function (value) {
                        let hi = value.charCodeAt(0);
                        let low = value.charCodeAt(1);
                        return '&#' + (((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000) + ';';
                    })
                    //@ts-ignore
                    .replace(NON_ALPHANUMERIC_REGEXP, function (value) {
                        return '&#' + value.charCodeAt(0) + ';';
                    })
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }
        },
        computed: {
            type() {
                const msg = this.message;
                if (msg) {
                    if ((msg.additional && msg.additional.offer)
                        || (msg.additionalPrivate && msg.additionalPrivate.offer)) {
                        return TYPE_OFFER;
                    }
                }

                return TYPE_REGULAR;
            },
            user() {
                if (this.message.mine === true)
                    return this.$store.state.user;

                return this.message.from;
            },
        },
        created() {
            this.setContent();
        }
    }
</script>

<style scoped>
    .chat-message {
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        min-width: 0;
    }

    .chat-message-content {
        display: block;
        padding: .25em .75em;
    }

    .chat-message-image {
        margin: -3px -1px -1px;
        overflow: hidden;
    }
</style>
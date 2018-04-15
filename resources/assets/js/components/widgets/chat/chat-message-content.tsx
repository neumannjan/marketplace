import api from 'JS/api';
import PlaceholderImg from "JS/components/widgets/image/placeholder-img.vue";
import {Location} from 'vue-router/types/router';
import Vue, {VNode} from 'vue';
import {Offer, User} from 'JS/api/types';

enum MessageType {
    Regular = 'regular',
    Offer = 'offer'
}

export default Vue.extend({
    name: "chat-message-content",
    components: {
        PlaceholderImg,
    },
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
    render(h): VNode {
        const messageContent = () => {
            let content = this.message.content;

            if (content && content !== '') {
                return content;
            } else {
                return <i>{this.$store.getters.trans('interface.message.removed')}</i>;
            }
        }

        const messageText = () => {
            let user: User | null;

            if (this.message.mine === true)
                user = this.$store.state.user;
            else
                user = this.message.from;

            if (this.type === MessageType.Regular) {
                if (this.inline && user)
                    return <span>{user.display_name}: {messageContent()}</span>;
                else
                    return <span class={this.inline ? undefined : "chat-message-content"}>{messageContent()}</span>;
            } else if (this.type === MessageType.Offer) {
                const OfferMsg = <i>{this.$store.getters.trans('interface.notice.user-buy', {
                    user: this.user.display_name
                })}</i>

                if (!this.inline && this.offer)
                    return <span class="chat-message-content"><h2 class="h5">{(this.offer as Offer).name}</h2>{OfferMsg}</span>
                else
                    return OfferMsg;
            }

            return <span class="chat-message-content"></span>;
        }

        const image = () => {
            if (this.imgSrc) {
                return (
                    <placeholder-img img-style="width: 100%"
                                     placeholder-style="height: 80px"
                                     placeholder-class="w-100"
                                     class="chat-message-image"
                                     style={{
                                         borderTopLeftRadius: `${this.imgSize / 2}px`,
                                         borderTopRightRadius: `${this.imgSize / 2}px`
                                     }}
                                     src={this.imgSrc}/>
                );
            } else {
                return undefined;
            }
        };

        const link = (content: any) => {
            if (this.route) {
                return (
                    <router-link to={this.route}
                                 class={[this.white ? 'text-white' : 'text-dark']}>
                        {content}
                    </router-link>
                );
            } else {
                return content;
            }
        }

        const ElementType = this.as;
        return (
            <ElementType class="chat-message">
                {image()}
                {link(messageText())}
            </ElementType>
        );
    },
    data: (): {
        offer: Offer | undefined,
        imgSrc: string | undefined,
        route: Location | undefined
    } => ({
        offer: undefined,
        imgSrc: undefined,
        route: undefined
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
                case MessageType.Offer:
                    this.setAsOffer();
                    break;
                case MessageType.Regular:
                default:
                    this.offer = undefined;
                    this.imgSrc = undefined;
                    this.route = undefined;
                    break;
            }
        },
        async setAsOffer() {
            if (this.inline) {
                this.offer = undefined;
                this.imgSrc = undefined;
                this.route = undefined;
                return;
            }

            let offer: Offer;
            if (!this.message.additionalPrivate || !this.message.additionalPrivate.offer) {
                if (typeof this.message.additional.offer === 'object') {
                    offer = this.offer = this.message.additional.offer;
                } else {
                    this.offer = undefined;
                    this.imgSrc = '';
                    this.route = {query: {offer: this.message.additional.offer}};

                    offer = this.offer = await api.requestSingle<Offer>('offer', {
                        scope: this.$store.getters.scope.offer,
                        id: this.message.additional.offer
                    });
                }
            } else {
                offer = this.offer = this.message.additionalPrivate.offer;
            }

            this.imgSrc = offer.images.length > 0 ? offer.images[0].urls.original : undefined;
            this.route = {query: {offer: offer.id.toString()}};
        }
    },
    computed: {
        type(): MessageType {
            const msg = this.message;
            if (msg) {
                if ((msg.additional && msg.additional.offer)
                    || (msg.additionalPrivate && msg.additionalPrivate.offer)) {
                    return MessageType.Offer;
                }
            }

            return MessageType.Regular;
        },
        user(): User {
            if (this.message.mine === true)
                return this.$store.state.user;

            return this.message.from;
        },
    },
    created() {
        this.setContent();
    }
});
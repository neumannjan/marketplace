import {ConversationMediatorInterface, IntermediateMessage, NormalizedMessage} from "JS/api/messaging/typings";
import {ContinuousResponse, Message, MessageAdditional, User} from "JS/api/types";
import api from "JS/api";
import EventPropagator, {EventPropagatorAttachFunction} from "JS/lib/event-propagator";
import {getConversationChannelName, isMine, normalizeMessage} from "JS/api/messaging/helpers";
import {Channel, ChannelType} from "JS/lib/echo/channel";
import echo from "JS/echo";
import store from "JS/store";
import {ConnectionManagerEvents} from "JS/lib/echo";
import {random} from "JS/app";

export enum ConversationEvents {
    Received = 'Received',
    Typing = 'Typing',
    Message = 'Message',
    Reconnect = 'Reconnect',
}

type ReceivedEventPayload = {
    id: number,
    read: boolean,
    username: string
};

interface TypingEventPayload {
    typing: boolean,
    username: string
}

interface Payloads {
    [ConversationEvents.Received]: ReceivedEventPayload,
    [ConversationEvents.Typing]: boolean,
    [ConversationEvents.Message]: NormalizedMessage,
    [ConversationEvents.Reconnect]: undefined,
}

export class ConversationMediator extends EventPropagator<Payloads, ConversationEvents> implements ConversationMediatorInterface {
    public readonly me: User;
    public readonly them: User;
    public readonly channel: Channel;

    constructor(user: User) {
        super(false);
        if (!store.state.user) {
            throw "Cannot join a conversation: user not logged in";
        }

        this.me = store.state.user;
        this.them = user;
        this.channel = echo.channel(ChannelType.Private, getConversationChannelName(this.me.username, user.username), false);

        this.doAttachSelf();
    }

    protected attachSelf(on: EventPropagatorAttachFunction): void {

        // typing event

        on(this.channel, 'typing', (payload: TypingEventPayload) => {
            if (payload.username === this.them.username) {
                this.dispatch(ConversationEvents.Typing, payload.typing);
            }
        }, true);


        // message received event

        const onMessageReceived = (payload: ReceivedEventPayload) => {
            if (payload.username === this.them.username) {
                this.dispatch(ConversationEvents.Received, payload);
            }
        };

        on(this.channel, 'received', onMessageReceived, true);
        on(this.channel, 'MessageReceived', onMessageReceived);


        // message sent event

        on(this.channel, 'MessageSent', (message: Message) => {
            this.dispatch(ConversationEvents.Message, normalizeMessage(message));
            if (!message.mine) {
                this.sendReceived(message, true);
            }
        });


        // reconnect event

        on(echo, ConnectionManagerEvents.Reconnect, () => {
            this.dispatch(ConversationEvents.Reconnect, undefined);
        });
    }

    /**
     * Fetch newest messages
     */
    fetchMessages(): () => Promise<ContinuousResponse<NormalizedMessage[]>> {
        return api.requestPaginated<NormalizedMessage[], Message[]>(`/api/messages?with=${this.them.username}`, (data) => {
            return data.map(normalizeMessage);
        });
    }

    /**
     * Send a message.
     * @param content {string}
     * @param additional {MessageAdditional}
     */
    sendMessage(content: string, additional: MessageAdditional): IntermediateMessage {
        const identifier = 'T' + random.int32().toString();

        const promise = api.requestSingle<Message>('message-send', {
            to: this.them.username,
            content: content,
            additional: additional,
            identifier: identifier
        })
            .then(normalizeMessage);

        return {
            identifier: identifier,
            promise: promise
        };
    }

    /**
     * Send 'user is typing' notification to user
     * @param typing {boolean}
     */
    sendTyping(typing: boolean): void {
        const payload: TypingEventPayload = {
            typing: typing,
            username: this.me.username
        }

        this.channel.whisper('typing', payload);
    }

    /**
     * Send a 'message was received' notification
     * @param message: {Message | number} Message or message ID.
     * @param read {boolean} Whether the user has read the message or only received it.
     */
    sendReceived(message: number | Message, read: boolean): void {
        if (typeof message === 'object' && isMine(message)) {
            return;
        }

        const payload: ReceivedEventPayload = {
            id: typeof message === 'object' ? message.id : message,
            read: read,
            username: this.me.username
        }

        this.channel.whisper('received', payload);
        api.requestSingle('message-received-notify', payload);
    }

    detach() {
        super.detach();
        this.channel.leave();
    }
}
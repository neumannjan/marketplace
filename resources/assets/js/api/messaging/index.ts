import {MessagingInterface, NormalizedMessage} from "JS/api/messaging/typings";
import {ContinuousResponse, Conversation, Message, MessageReceivedNotifyRequest, User} from "JS/api/types";
import api from "JS/api";
import allEvents, {Events as AppEvents} from 'JS/events';
import {ConversationMediator} from "JS/api/messaging/conversation";
import EventPropagator, {EventPropagatorAttachFunction} from "JS/lib/event-propagator";
import {isMine} from "JS/api/messaging/helpers";

const CONVERSATIONS_URL = '/api/conversations';

export enum MessagingEvents {
    Message = 'Message'
}

interface Payloads {
    [MessagingEvents.Message]: NormalizedMessage
}

export default new class Messaging extends EventPropagator<Payloads, MessagingEvents> implements MessagingInterface {

    protected attachSelf(on: EventPropagatorAttachFunction): void {
        on(allEvents, AppEvents.MessageSent, (message) => {
            this.dispatch(MessagingEvents.Message, message);
            this.sendReceived(message, false);
        });
    }

    /**
     * Fetch conversations
     */
    fetchConversations(): () => Promise<ContinuousResponse<Conversation[]>> {
        return api.requestPaginated(CONVERSATIONS_URL);
    }

    /**
     * Join a conversation
     * @param withUser {User}
     */
    joinConversation(withUser: User): ConversationMediator {
        return new ConversationMediator(withUser);
    }

    /**
     * Send a 'message was received' notification
     * @param message: {Message | number} Message or message ID.
     * @param read {boolean} Whether the user has read the message or only received it.
     */
    sendReceived(message: Message | number, read: boolean): void {
        if (typeof message === 'object' && isMine(message)) {
            return;
        }

        const data: MessageReceivedNotifyRequest = {
            id: typeof message === 'object' ? message.id : message,
            read: read
        }

        api.requestSingle('message-received-notify', data);
    }
}
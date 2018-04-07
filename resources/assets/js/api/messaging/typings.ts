import {ContinuousResponse, Conversation, Message, MessageAdditional, User} from "JS/api/types";

export interface NormalizedMessage extends Message {
    mine: boolean
}

export interface IntermediateMessage {
    identifier: string,
    promise: Promise<NormalizedMessage>
}

export interface MessageEndpointInterface {
    /**
     * Dispose of all listeners
     */
    dispose(): void;
}

export interface MessagingInterface extends MessageEndpointInterface {
    /**
     * Fetch conversations
     */
    fetchConversations(): () => Promise<ContinuousResponse<Conversation[]>>;

    /**
     * Join a conversation
     * @param withUser {User}
     */
    joinConversation(withUser: User): ConversationMediatorInterface;
}

export interface ConversationMediatorInterface extends MessageEndpointInterface {
    /**
     * Fetch newest messages with user
     */
    fetchMessages(): () => Promise<ContinuousResponse<NormalizedMessage[]>>;

    /**
     * Send a message.
     * @param content {string}
     * @param additional {MessageAdditional}
     */
    sendMessage(content: string, additional: MessageAdditional): IntermediateMessage;

    /**
     * Send 'user is typing' notification to user
     * @param typing {boolean}
     */
    sendTyping(typing: boolean): void;
}
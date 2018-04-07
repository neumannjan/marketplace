import {Message} from "JS/api/types";
import store from "JS/store";
import {NormalizedMessage} from "JS/api/messaging/typings";

export function normalizeMessage(message: Message): NormalizedMessage {
    message.mine = isMine(message);
    return message as NormalizedMessage;
}

export function isMine(message: Message): boolean {
    return message.mine === true || (!!store.state.user && store.state.user.username === message.from.username);
}

const CONVERSATION_CHANNEL_PREFIX = 'conversation';

export function getConversationChannelName(username1: string, username2: string): string {
    if (username1 <= username2)
        return `${CONVERSATION_CHANNEL_PREFIX}.${username1}.${username2}`;
    else
        return `${CONVERSATION_CHANNEL_PREFIX}.${username2}.${username1}`;
}
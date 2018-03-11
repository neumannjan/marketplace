import EventListener from "JS/lib/event-listener";

export enum Events {
    MessageSent = 'MessageSent',
    MessageSentOther = 'MessageSentOther',
    UnreadConversations = 'UnreadConversations',
    RequestPopup = 'RequestPopup',
    RequestBuy = 'RequestBuy',
    ViewportChange = 'ViewportChange',
}

export interface RequestPopupPayload<T extends string = string> {
    type: T,
    then(): void
}

export const events = new EventListener<Events>();
export default events;
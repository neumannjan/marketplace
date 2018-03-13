import EventListener from "JS/lib/event-listener";

export enum Events {
    MessageSent = 'MessageSent',
    UnreadConversations = 'UnreadConversations',
    RequestPopup = 'RequestPopup',
    RequestBuy = 'RequestBuy',
    ViewportChange = 'ViewportChange',
}

export const events = new EventListener<Events>();
export default events;

// event types

export interface RequestPopupPayload<T extends string = string> {
    type: T,
    then(): void
}
import EventListener from "JS/lib/event-listener";

export enum Events {
    MessageSent, MessageSentOther, UnreadConversations, RequestPopup, RequestBuy, ViewportChange
}

export const events = new EventListener<Events>();
export default events;
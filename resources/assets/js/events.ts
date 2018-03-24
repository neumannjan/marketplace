import EventListener from "JS/lib/event-listener";
import { NormalizedMessage } from "JS/api/messaging/typings";
import { Conversation, Offer } from "JS/api/types";

export enum Events {
    MessageSent = 'MessageSent',
    UnreadConversations = 'UnreadConversations',
    RequestPopup = 'RequestPopup',
    RequestBuy = 'RequestBuy',
    ViewportChange = 'ViewportChange',

    /**
     * Dispatch to refresh current view
     */
    AppRefresh = 'AppRefresh',
    
    AfterAppRefresh = 'AfterAppRefresh',
    OfferRemoved = 'OfferRemoved',
    OfferModified = 'OfferModified',
}

interface Payloads {
    [Events.MessageSent]: NormalizedMessage,
    [Events.UnreadConversations]: Conversation[],
    [Events.RequestPopup]: any,
    [Events.RequestBuy]: any,
    [Events.ViewportChange]: boolean,
    [Events.AppRefresh]: undefined,
    [Events.AfterAppRefresh]: undefined,
    [Events.OfferRemoved]: number,
    [Events.OfferModified]: Offer,
}

export const events = new EventListener<Payloads, Events>();
export default events;

// event types

export interface RequestPopupPayload<T extends string = string> {
    type: T,
    then(): void
}
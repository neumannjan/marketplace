import EventListener from "JS/lib/event-listener";
import {NormalizedMessage} from "JS/api/messaging/typings";
import {Conversation, Offer, User} from "JS/api/types";
import {FloatingButtonTypes} from "JS/components/types";

export enum Events {
    MessageSent = 'MessageSent',
    UnreadConversations = 'UnreadConversations',
    RequestPopup = 'RequestPopup',
    RequestBuy = 'RequestBuy',
    RequestChat = 'RequestChat',
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
    [Events.RequestPopup]: RequestPopupPayload,
    [Events.RequestBuy]: Offer,
    [Events.RequestChat]: User,
    [Events.ViewportChange]: boolean,
    [Events.AppRefresh]: undefined,
    [Events.AfterAppRefresh]: undefined,
    [Events.OfferRemoved]: number,
    [Events.OfferModified]: Offer
}

export const events = new EventListener<Payloads, Events>();
export default events;

// event types

export interface RequestPopupPayload {
    type: FloatingButtonTypes,

    then(): void
}
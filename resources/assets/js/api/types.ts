/**
 * Image interface
 */
export interface Image {
    id: number,
    width: number,
    height: number,
    urls: {
        original?: string,
        tiny?: string,
        icon?: string,
        icon_2x?: string,
        thumbnail?: string,
    },
    ready: boolean
}

export enum UserStatus {
    Inactive = 0,
    Active = 1,
    Banned = 2
}

/**
 * User interface
 */
export interface User {
    username: string,
    email: string,
    display_name: string,
    status: UserStatus,
    description: string,
    profile_image: Image | null
}

export enum OfferStatus {
    Draft = 0,
    Available = 1,
    Sold = 2
}

/**
 * Offer interface
 */
export interface Offer {
    id: number,
    name: string,
    author: User,
    listed_at: string,
    price?: string,
    price_value: number,
    currency: string,
    description: string,
    status: OfferStatus,
    images: Image[]
}

/**
 * Offer with additional information available to its owners
 */
export interface ExtendedOffer extends Offer {
    bumps_left: number,
    just_bumped: boolean,
    expired: boolean,
}

/**
 * Offer with additional information available to admins
 */
export interface AdminOffer extends ExtendedOffer {
    reported_times: number
}

/**
 * Determine whether an offer is ExtendedOffer
 * @param offer {Offer}
 */
export function isExtendedOffer(offer: Offer): offer is ExtendedOffer {
    return (<ExtendedOffer>offer).expired !== undefined;
}

/**
 * Determine whether an offer is AdminOffer
 * @param offer {Offer}
 */
export function isAdminOffer(offer: Offer): offer is AdminOffer {
    return (<AdminOffer>offer).reported_times !== undefined;
}

export interface MessageAdditional {
    offer?: Offer | number
}

export interface MessageBase {
    id: number,
    content: string,
    additional: MessageAdditional,
    from: User,
    read: boolean,
    mine?: boolean
}

/**
 * Message interface
 */
export interface Message extends MessageBase {
    identifier?: string,    
    to: User,
    received: boolean,
}

/**
 * Conversation interface
 */
export interface Conversation extends MessageBase {
    user: User
}

//------------------------------------

export interface FlashMessage {
    type: 'success' | 'status' | 'danger' | 'warning' | 'primary' | 'secondary'
    message: string
}

export interface FlashMessageWithKey extends FlashMessage {
    key: string
}

export interface TranslationMessages {
    [key: string]: string | TranslationMessages
}

export interface Currencies {
    [code: string]: number
}

/**
 * Possible authorization scopes of a database fetch request
 */
export type RequestScope = 'unlimited' | 'public';

/**
 * Possible authorization scopes of an offer fetch request
 */
export type OfferRequestScope = RequestScope | 'auth';

/**
 * Global request response
 */
export interface GlobalResponse {
    name: string,
    token: null | string,
    locale: string,
    fallback_locale: string,
    available_locales: string[],
    user: null | User,
    is_admin: boolean,
    flash: {[key: string]: FlashMessage},
    socket_host: null | string
}

/**
 * Initial request response
 */
export interface InitialResponse extends GlobalResponse {
    messages: TranslationMessages,
    unread_conversations?: Conversation[],
    max_file_uploads: number
}

/**
 * Cached request response
 */
export interface CachedResponse {
    currencies: Currencies
}

/**
 * Login response
 */
export interface LoginResponse {
    unread_conversations: Conversation[]
}

export interface PaginatedResponse<Data extends Array<any>> {
    data: Data,
    first_page_url: string,
    next_page_url: string | null,
    path: string,
    per_page: number,
    count: number
}

/**
 * Response that can request more data
 */
export type ContinuousResponse<T> = {
    data: T;

    /**
     * Fetch more data
     */
    fetchMore: (() => Promise<ContinuousResponse<T>>) | null;
}

export type MessageReceivedNotifyRequest = {read?: boolean} & ({id: number} | {ids: number[]})
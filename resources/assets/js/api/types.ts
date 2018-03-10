/**
 * Image interface
 */
export interface Image {
    id: number,
    width: number,
    height: number,
    urls: string[],
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
    profile_image: Image
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
    id: string,
    name: string,
    author: User,
    price: string,
    description: string,
    status: OfferStatus,
    expired: boolean,
    images: Image[]
}

export interface MessageBase {
    id: number,
    content: string,
    additional: {
        offer?: Offer
    },
    from: User,
    read: boolean
}

/**
 * Message interface
 */
export interface Message extends MessageBase {
    identifier: string | null,
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
    validation?: {
        min?: string,
        max?: string,
        required?: string,
        slug?: string,
        numeric?: string,
        containsNumeric?: string,
        containsNonNumeric?: string,
        confirmed?: string,
        email?: string,
        image?: string,
    }
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
    token: null | string,
    locale: string,
    is_authenticated: boolean,
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
    unread_conversations?: Conversation[]
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

export interface PaginatedResponse<Data> {
    data: Data,
    first_page_url: string,
    next_page_url: string,
    path: string,
    per_page: number,
    count: number
}
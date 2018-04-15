export type NotificationType =
    'primary' |
    'secondary' |
    'success' |
    'danger' |
    'warning' |
    'info' |
    'light' |
    'dark';

/**
 * Anonymous (without ID) notification declaration
 */
export interface AnonymousNotification {
    message: string,
    type: NotificationType,
    persistent?: boolean
}

/**
 * Notification declaration
 */
export interface Notification<Identifier extends string = string> extends AnonymousNotification {
    id: Identifier
}

/**
 * Notification manager interface
 */
export interface NotificationManagerInterface<Identifier extends string = string> {

    /**
     * Show a notification.
     * @param notification {AnonymousNotification | Notification<Identifier>}
     * @returns Notification identifier
     */
    showNotification(notification: AnonymousNotification | Notification<Identifier>): string;

    /**
     * Hide a notification.
     * @param identifier {string | Identifier}
     */
    hideNotification(identifier: string | Identifier): void;

    /**
     * Whether a notification is shown.
     * @param identifier {string | Identifier}
     */
    isShown(identifier: Identifier | string): boolean;

    /**
     * Force a particular notification ID to be hidden
     * @param identifier {string | Identifier}
     * @param hidden {boolean}
     */
    forceHidden(identifier: string | Identifier, hidden?: boolean): void;
}
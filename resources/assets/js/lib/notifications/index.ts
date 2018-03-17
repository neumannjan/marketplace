import { NotificationManagerInterface, Notification, NotificationType, AnonymousNotification } from "./typings";
import EventListener from "JS/lib/event-listener";

export enum NotificationEvents {
    Shown = 'Shown',
    Hidden = 'Hidden',
}

interface Payloads<Identifier extends string> {
    [NotificationEvents.Shown]: AnonymousNotification | Notification<Identifier>
    [NotificationEvents.Hidden]: string
}

function isIdentifierNotification<Identifier extends string>(notification: AnonymousNotification | Notification<Identifier>): notification is Notification<Identifier> {
    return !!(<Notification<Identifier>> notification).id;
}

export default abstract class NotificationManager<Identifier extends string = string> extends EventListener<Payloads<Identifier>, NotificationEvents> implements NotificationManagerInterface<Identifier> {
    protected hidden: {[index: string]: boolean} = {};

    protected abstract doShowNotification(notification: Notification<Identifier | string>): void;
    protected abstract doHideNotification(identifier: Identifier | string): void;

    showNotification(notification: AnonymousNotification | Notification<Identifier>): string {
        let identifier: string;

        if(!isIdentifierNotification(notification)) {
            identifier = 'T' + Date.now().toString();
            (notification as Notification).id = identifier;
        } else {
            identifier = notification.id;

            if(this.hidden[identifier] === true) {
                return identifier;
            }
        }

        this.doShowNotification(notification as Notification);
        this.dispatch(NotificationEvents.Shown, notification);

        return identifier;
    }

    hideNotification(identifier: Identifier | string): void {
        this.doHideNotification(identifier);
        this.dispatch(NotificationEvents.Hidden, identifier);
    }

    forceHidden(identifier: string | Identifier, hidden: boolean = false): void {
        this.hidden[identifier] = hidden;

        if(hidden) {
            this.hideNotification(identifier);
        }
    }
}
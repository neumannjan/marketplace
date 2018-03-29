import NotificationManager from "./lib/notifications";
import store from 'JS/store';
import {Notification} from "JS/lib/notifications/typings";

export enum NotificationTypes {
    NewMessages = 'NewMessages',

    HttpConnection = 'HttpConnection',
    NoHttpConnection = 'NoHttpConnection',
    WebsocketConnection = 'WebsocketConnection',
    NoWebsocketConnection = 'NoWebsocketConnection',
}

export default new class Notifications extends NotificationManager<NotificationTypes> {

    protected doShowNotification(notification: Notification<NotificationTypes | string>): void {
        store.commit('addNotification', notification);
    }
    
    protected doHideNotification(identifier: string): void {
        store.commit('removeNotification', identifier);
    }
}
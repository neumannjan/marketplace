import notifications, {NotificationTypes} from "JS/notifications";
import { NotificationType } from "JS/lib/notifications/typings";

/**
 * Safely retrieves a value from a nested object. Returns defaultValue if the key is not present in the object.
 * @param {object} object
 * @param {string | Array<string>} accessor Dot notation or an array of keys
 * @param defaultValue Value to be returned
 * @return Value or defaultValue
 */
export function safeGet(object: object, accessor: string | Array<string>, defaultValue: any = null): any {
    if (typeof accessor === 'string') {
        accessor = accessor.split('.');
    }

    return accessor.reduce(function (a: any, b: string) {
        if (a && (typeof a === 'object') && a[b])
            return a[b];
        else
            return null;
    }, object);
}

/**
 * Return a promise that resolves once an event is triggered.
 * @param {EventTarget} target
 * @param {string} type
 * @return {Promise<Event>}
 */
export function awaitEvent(target: EventTarget, type: string): Promise<any> {
    return new Promise((resolve: (event: Event) => void) => {
        target.addEventListener(type, event => {
            resolve(event);
        }, {
            once: true
        });
    });
}

/**
 * Function which takes an image file and returns its scaled down thumbnail.
 * A callback is provided and first called for the URL of the original image, then for the thumbnail URL.
 * @param image 
 * @param maxWidth 
 * @param maxHeight 
 */
export async function getImageFileThumbnailDataURL(image: File, maxWidth = 400, maxHeight = 400): Promise<string> {
    const onLoad = (target: EventTarget, after: () => void) => {
        return new Promise(resolve => {
            target.addEventListener('load', resolve, {once: true});
            after();
        });
    }

    const reader = new FileReader();

    await onLoad(reader, () => {
        reader.readAsDataURL(image);
    });

    const fullURL = reader.result;

    const img = document.createElement("img");
    const canvas = document.createElement("canvas");

    await onLoad(img, () => {
        img.src = fullURL;
    });

    let width = img.width;
    let height = img.height;

    if (width > height) {
        if (width > maxWidth) {
            height *= maxWidth / width;
            width = maxWidth;
        }
    } else {
        if (height > maxHeight) {
            width *= maxHeight / height;
            height = maxHeight;
        }
    }

    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext("2d");

    if(ctx) {
        ctx.drawImage(img, 0, 0, width, height);
        const thumbURL = canvas.toDataURL(image.type);
        return thumbURL;
    }

    console.warn('Failed to create an image thumbnail');
    return fullURL;
}

interface NotificationConfiguration {
    id?: NotificationTypes | string;
    message: string;
    type?: NotificationType
}

interface ActionConfiguration {
    confirm?: string,
    beforeNotification?: NotificationConfiguration | string,
    afterNotification?: NotificationConfiguration | string,
    errorNotification?: NotificationConfiguration | string,
}

/**
 * Does a basic action that may require confirmation and may display notifications
 * @param config
 * @param func
 */
export function doAction(config: ActionConfiguration, func: () => Promise<any>) {
    if (!config.confirm || confirm(config.confirm)) {
        function notificationType(notification: NotificationConfiguration | string | undefined, defaultValue: NotificationType) {
            if(typeof notification === 'string')
                return defaultValue;
            else if(notification && notification.type)
                return notification.type;
            else
                return defaultValue;
        }

        function notificationMessage(notification: NotificationConfiguration | string | undefined, defaultValue: string = '') {
            if(typeof notification === 'string')
                return notification;
            else if(notification && notification.message)
                return notification.message;
            else
                return defaultValue;
        }

        let notificationID: string;
        if (config.beforeNotification) {
            notificationID = notifications.showNotification({
                type: notificationType(config.beforeNotification, 'info'),
                message: notificationMessage(config.beforeNotification),
                persistent: true
            });
        }

        func().then(() => {
            if (config.beforeNotification) {
                notifications.hideNotification(notificationID);
            }

            if (config.afterNotification) {
                notifications.showNotification({
                    type: notificationType(config.afterNotification, 'success'),
                    message: notificationMessage(config.afterNotification),
                    persistent: false
                });
            }
        }).catch(() => {
            if (config.beforeNotification) {
                notifications.hideNotification(notificationID);
            }

            notifications.showNotification({
                type: notificationType(config.errorNotification, 'danger'),
                message: notificationMessage(config.errorNotification, 'ERROR'), //TODO translate
                persistent: false
            });
        });
    }
}
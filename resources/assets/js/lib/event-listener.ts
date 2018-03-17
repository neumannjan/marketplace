/**
 * An event callback
 */
export type EventCallback<Payloads extends EventListenerPayloads, T extends keyof Payloads> = (payload: Payloads[T]) => void;

/**
 * Declaration of event listener payloads
 */
export interface EventListenerPayloads {
    [index: string]: any
}

/**
 * EventListener class. Allows to bind and unbind event callbacks.
 */
export default class EventListener<Payloads extends EventListenerPayloads = any, Name extends keyof Payloads = keyof Payloads> {

    /**
     * An array of callbacks per event name
     */
    protected callbacks: {[eventName: string]: ((payload: keyof Payloads) => void)[]} = {};

    /**
     * Attach an event listener to an event.
     * @param {string} name
     * @param {Function} callback
     */
    on<T extends Name>(name: T, callback: EventCallback<Payloads, T>) {
        if (this.callbacks[name] === undefined) {
            this.callbacks[name] = [];
        }

        this.callbacks[name].push(callback);
    }

    /**
     * Detach an event listener from an event.
     * @param {string} name
     * @param {Function} callback
     */
    off<T extends Name>(name: T, callback: EventCallback<Payloads, T>) {
        if (this.callbacks[name]) {
            const index = this.callbacks[name].indexOf(callback);
            if (index >= 0) {
                this.callbacks[name].splice(index, 1);
            }
        }
    }

    /**
     * Attach an event listener to an event, but only once.
     * @param {string} name
     * @param {Function} callback
     */
    once<T extends Name>(name: T, callback: EventCallback<Payloads, T>) {
        const c = (payload: Payloads[T]) => {
            callback(payload);
            this.off(name, c);
        };

        this.on(name, c);
    }

    /**
     * Dispatch an event.
     * @param {string | number} name
     * @param params Parameters to pass to each callback.
     */
    dispatch<T extends Name>(name: T, payload: Payloads[T]) {
        if (this.callbacks[name]) {
            for (let callback of this.callbacks[name]) {
                callback(payload);
            }
        }
    }
}
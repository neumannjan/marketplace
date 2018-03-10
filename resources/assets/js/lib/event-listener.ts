/**
 * An array of callbacks per event name
 */
export interface EventListenerMap {
    [eventName: string]: EventCallback[],
    [eventName: number]: EventCallback[]
}

type EventNames = string | number;

/**
 * An event callback
 */
export type EventCallback = (...params: any[]) => void;

/**
 * EventListener class. Allows to bind and unbind event callbacks.
 */
export default class EventListener<Names extends EventNames = string> {

    /**
     * An array of callbacks per event name
     */
    protected callbacks: EventListenerMap;

    constructor() {
        this.callbacks = {};
    }

    /**
     * Attach an event listener to an event.
     * @param {string} name
     * @param {EventCallback} callback
     */
    on(name: Names, callback: EventCallback) {
        if (this.callbacks[<EventNames> name] === undefined) {
            this.callbacks[<EventNames> name] = [];
        }

        this.callbacks[<EventNames> name].push(callback);
    }

    /**
     * Detach an event listener from an event.
     * @param {string} name
     * @param {EventCallback} callback
     */
    off(name: Names, callback: EventCallback) {
        if (this.callbacks[<EventNames> name]) {
            const index = this.callbacks[<EventNames> name].indexOf(callback);
            if (index >= 0) {
                this.callbacks[<EventNames> name].splice(index, 1);
            }
        }
    }

    /**
     * Attach an event listener to an event, but only once.
     * @param {string} name
     * @param {EventCallback} callback
     */
    once(name: Names, callback: EventCallback) {
        const c = (...params: any[]) => {
            callback(...params);
            this.off(name, c);
        };

        this.on(name, c);
    }

    /**
     * Dispatch an event.
     * @param {string} name
     * @param params Parameters to pass to each callback.
     */
    dispatch(name: Names, ...params: any[]) {
        if (this.callbacks[<EventNames> name]) {
            for (let callback of this.callbacks[<EventNames> name]) {
                callback(...params);
            }
        }
    }
}
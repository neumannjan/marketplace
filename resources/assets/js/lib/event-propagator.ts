import EventListener, {EventListenerPayloads} from "JS/lib/event-listener";
import {Channel} from "JS/lib/echo/channel";

interface EventPropagatorCallback {
    listener: EventListener<any, any>,
    name: string,
    func: (payload: any) => void,
    whisper: boolean
}

/**
 * Function that EventPropagator classes should call to attach to EventListeners and listen to events
 */
export interface EventPropagatorAttachFunction {
    <Payloads extends EventListenerPayloads, T extends keyof Payloads>
    (listener: EventListener<Payloads, string>, event: T, func: (payload: Payloads[T]) => void, whisper?: boolean): void
}

/**
 * Class that attaches to EventListener and listens to its events only throughout its lifetime.
 */
export default abstract class EventPropagator<Payloads extends EventListenerPayloads = any, Events extends keyof Payloads = keyof Payloads>
    extends EventListener<Payloads, Events> {
    private watchedCallbacks: Array<EventPropagatorCallback> = [];

    /**
     * Function that calls the EventPropagatorAttachFunction.
     * @param on {EventPropagatorAttachFunction}
     */
    protected abstract attachSelf(on: EventPropagatorAttachFunction): void;

    /**
     *
     * @param doAttachSelf {boolean} Whether the constructor should call the attachSelf function. If false, doAttachSelf should be called in constructor.
     */
    constructor(doAttachSelf: boolean = true) {
        super();
        if (doAttachSelf) {
            this.doAttachSelf();
        }
    }

    /**
     * Function that calls the attachSelf function. Should be called in constructor.
     */
    protected doAttachSelf() {
        this.attachSelf((listener, event, func, whisper = false) => {
            this.watchedCallbacks.push({
                listener: listener,
                name: event,
                func: func,
                whisper: whisper
            });

            if (whisper)
                (<Channel>listener).onWhisper(event, func);
            else
                listener.on(event, func);
        });
    }

    /**
     * Detach from all EventListeners.
     */
    protected detach() {
        for (let callback of this.watchedCallbacks) {
            if (callback.whisper)
                (<Channel>callback.listener).offWhisper(callback.name, callback.func);
            else
                callback.listener.off(callback.name, callback.func);
        }

        this.watchedCallbacks = [];
    }

    /**
     * Dispose of all listeners. Should be called before the instance is garbage collected.
     */
    dispose() {
        this.detach();
    }
}
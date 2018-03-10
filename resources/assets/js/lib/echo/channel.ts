import {Channel as LaravelEchoChannel, PresenceChannel as _PresenceChannel} from "laravel-echo/src/channel";
import EventListener, {EventCallback} from "resources/assets/js/lib/event-listener";
import Echo from "laravel-echo";

export enum ChannelType {
    Public = 'public',
    Private = 'private',
    Presence = 'presence',
}

const WHISPER_EVENT_PREFIX = 'whisper-';

interface LaravelEchoPrivateChannel extends LaravelEchoChannel {
    whisper(eventName: any, data: any): this;
}

interface LaravelEchoPresenceChannel extends LaravelEchoPrivateChannel, _PresenceChannel {
}

function isPrivateChannel(channel: LaravelEchoChannel): channel is LaravelEchoPrivateChannel {
    return 'whisper' in channel;
}

function isPresenceChannel(channel: LaravelEchoChannel): channel is LaravelEchoPresenceChannel {
    return 'joining' in channel;
}

/**
 * Get channel instance by type and name
 * @param {Echo} echo
 * @param {string} type
 * @param {string} name
 * @return {LaravelEchoChannel}
 */
export function getLaravelEchoChannelByType(echo: Echo, type: ChannelType, name: string): LaravelEchoChannel {
    switch (type) {
        case ChannelType.Private:
            return (<LaravelEchoPrivateChannel> echo.private(name));
        case ChannelType.Presence:
            return (<LaravelEchoPresenceChannel> echo.join(name));
        case ChannelType.Public:
            return echo.channel(name);
    }
}

/**
 * Channel class.
 * Allows to bind and unbind event callbacks including whisper events. Allows to trigger client events as well.
 */
export class Channel extends EventListener {
    private _echo: Echo | null = null;
    private channel: LaravelEchoChannel | null = null;

    readonly type: ChannelType;
    readonly name: string;

    constructor(type: ChannelType, name: string) {
        super();
        this.type = type;
        this.name = name;
    }

    public set echo(echo: Echo | null) {
        this._echo = echo;
        this.channel = echo ? getLaravelEchoChannelByType(echo, this.type, this.name) : null;
        this.attachListeners(Object.keys(this.callbacks));
    }

    public get echo() {
        return this._echo;
    }

    on(name: string, callback: EventCallback): void {
        super.on(name, callback);
        this.attachListeners(name);
    }

    /**
     * Attach event listeners to the real Laravel Echo channel instance.
     * @param {string | string[]} eventNames
     */
    protected attachListeners(eventNames: string | string[]) {
        if (this.channel) {
            if (!Array.isArray(eventNames)) {
                eventNames = [eventNames];
            }

            for (let name of eventNames) {
                if (name.startsWith(WHISPER_EVENT_PREFIX)) {

                    //remove the whisper prefix
                    name = name.substr(WHISPER_EVENT_PREFIX.length);

                    //dispatch on websocket event
                    this.channel.listenForWhisper(name, (...params: any[]) => {
                        this.dispatchWhisper(name, ...params);
                    });
                } else {
                    //dispatch on websocket event
                    this.channel.listen(name, (...params: any[]) => {
                        this.dispatch(name, ...params);
                    });
                }
            }
        }
    }

    /**
     * Attach an event listener to a whisper event.
     * @param {string} name
     * @param {EventCallback} callback
     */
    onWhisper(name: string, callback: EventCallback) {
        this.on(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Detach an event listener from a whisper event.
     * @param {string} name
     * @param {EventCallback} callback
     */
    offWhisper(name: string, callback: EventCallback) {
        this.off(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Attach an event listener to a whisper event, but only once.
     * @param {string} name
     * @param {EventCallback} callback
     */
    onceWhisper(name: string, callback: EventCallback) {
        this.once(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Dispatch a whisper event.
     * @param {string} name
     * @param params Parameters to pass to each callback.
     */
    dispatchWhisper(name: string, ...params: any[]) {
        this.dispatch(WHISPER_EVENT_PREFIX + name, ...params);
    }

    /**
     * Trigger a client event.
     * @param {string} name Event name
     * @param {object} data Event data
     */
    whisper(name: string, data: object) {
        if (this.channel) {
            if (this.type === ChannelType.Private) {
                (<LaravelEchoPrivateChannel> this.channel).whisper(name, data);
            }
        } else {
            throw "Cannot whisper: WebSocket server unavailable.";
        }
    }
}
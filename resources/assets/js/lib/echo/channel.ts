import {Channel as LaravelEchoChannel, PresenceChannel as _PresenceChannel} from "JS/lib/types/laravel-echo/channel";
import EventListener, { EventCallback } from "JS/lib/event-listener";
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
    private readonly leaveFunc: () => void;

    readonly type: ChannelType;
    readonly name: string;

    constructor(type: ChannelType, name: string, echo: Echo | null, leaveFunc: () => void) {
        super();
        this.type = type;
        this.name = name;
        this.echo = echo;
        this.leaveFunc = leaveFunc;
    }

    public set echo(echo: Echo | null) {
        this._echo = echo;
        this.channel = echo ? getLaravelEchoChannelByType(echo, this.type, this.name) : null;
        this.attachListeners(Object.keys(this.callbacks));
    }

    public get echo() {
        return this._echo;
    }

    on(name: string, callback: EventCallback<any, any>): void {
        super.on(name, callback);
        this.attachListeners(name);
    }

    leave(): void {
        this.leaveFunc();
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
                    this.channel.listenForWhisper(name, (payload: any) => {
                        this.dispatchWhisper(name, payload);
                    });
                } else {
                    //dispatch on websocket event
                    this.channel.listen(name, (payload: any) => {
                        this.dispatch(name, payload);
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
    onWhisper(name: string, callback: EventCallback<any, any>) {
        this.on(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Detach an event listener from a whisper event.
     * @param {string} name
     * @param {EventCallback} callback
     */
    offWhisper(name: string, callback: EventCallback<any, any>) {
        this.off(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Attach an event listener to a whisper event, but only once.
     * @param {string} name
     * @param {EventCallback} callback
     */
    onceWhisper(name: string, callback: EventCallback<any, any>) {
        this.once(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Dispatch a whisper event.
     * @param {string} name
     * @param params Parameters to pass to each callback.
     */
    dispatchWhisper(name: string, payload: any) {
        this.dispatch(WHISPER_EVENT_PREFIX + name, payload);
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
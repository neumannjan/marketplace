import {Channel, ChannelType} from "JS/lib/echo/channel";
import * as io from 'socket.io-client';
import Echo from "laravel-echo";
import EventListener from "JS/lib/event-listener";

type ChannelMap = {
    [type in ChannelType]: {
        [name: string]: Channel
    };
}

export enum ConnectionManagerEvents {
    Connect = 'Connect',
    Reconnect = 'Reconnect',
    Disconnect = 'Disconnect',
    ConnectError = 'ConnectError',
    ConnectTimeout = 'ConnectTimeout',
    Reconnecting = 'Reconnecting',
    ReconnectFailed = 'ReconnectFailed',
}

/**
 * Laravel Echo connection manager
 */
export default class ConnectionManager extends EventListener<ConnectionManagerEvents> implements Iterable<Channel> {
    protected channels: ChannelMap = {
        presence: {},
        private: {},
        public: {}
    };

    public* [Symbol.iterator](): Iterator<Channel> {
        for(let channels of Object.values(this.channels)) {
            for(let channel of Object.values(channels)) {
                yield channel;
            }
        }
    };

    protected echo: Echo | null = null;
    protected host: string | null = null;
    protected csrfToken: string | null = null;

    /**
     * Replace the internal Echo instance
     * @param {Echo | null} echo
     * @param {string | null} host
     * @param {string | null} csrfToken
     */
    private replaceEcho(echo: Echo | null, host: string | null, csrfToken: string | null) {
        if(this.echo) {
            this.echo.disconnect();
        }

        this.echo = echo;
        this.host = host;
        this.csrfToken = csrfToken;

        for(let channel of this) {
            channel.echo = echo;
        }
    }

    /**
     * Connect or reconnect to a host.
     * @param {string | null} host
     * @param {string | null} csrfToken
     * @param {boolean} forceReconnect
     */
    connect(host: string | null, csrfToken: string | null, forceReconnect: boolean = false) {
        if (host && csrfToken) {
            if (forceReconnect || host !== this.host || csrfToken !== this.csrfToken) {
                const echo = new Echo({
                    broadcaster: 'socket.io',
                    host: host,
                    csrfToken: csrfToken,
                    client: io
                });
        
                this.replaceEcho(echo, host, csrfToken);
        
                if (echo.connector.socket) {
                    const socket: EventTarget = echo.connector.socket;
                    socket.addEventListener('connect', () => this.dispatch(ConnectionManagerEvents.Connect));
                    socket.addEventListener('connect_error', e => this.dispatch(ConnectionManagerEvents.ConnectError, e));
                    socket.addEventListener('connect_timeout', e => this.dispatch(ConnectionManagerEvents.ConnectTimeout, e));
                    socket.addEventListener('reconnect', () => this.dispatch(ConnectionManagerEvents.Reconnect));
                    socket.addEventListener('reconnecting', num => this.dispatch(ConnectionManagerEvents.Reconnecting, num));
                    socket.addEventListener('reconnect_failed', e => this.dispatch(ConnectionManagerEvents.ReconnectFailed, e));
                    socket.addEventListener('disconnect', reason => this.dispatch(ConnectionManagerEvents.Disconnect, reason));
                }
            }
        } else {
            throw "Failed to connect to websocket: missing host or token."
        }
    }

    /**
     * Reconnect to the host.
     */
    reconnect() {
        this.connect(this.host, this.csrfToken, true);
    }

    /**
     * Disconnect from a host.
     */
    disconnect() {
        this.replaceEcho(null, null, null);
    }

    /**
     * Get a Channel instance.
     * @param {ChannelType} type Channel type.
     * @param {string} name Channel name.
     * @returns {Channel}
     */
    channel(type: ChannelType, name: string) {
        if(!this.channels[type][name]) {
            return this.channels[type][name] = new Channel(type, name, this.echo);
        } else {
            return this.channels[type][name];
        }
    }

    /**
     * Leave a channel
     * @param {string} name
     */
    leave(name: string) {
        for(let type of [ChannelType.Private, ChannelType.Presence, ChannelType.Public]) {
            if(this.channels[type][name]) {
                if(this.echo) {
                    this.echo.leave(name);
                }

                delete this.channels[type][name];
            }
        }
    }
}
import io from 'socket.io-client';
import Echo from 'laravel-echo';
import store from 'JS/store';

/**
 * EventListener class. Allows to bind and unbind event callbacks.
 */
class EventListener {
    constructor() {
        /** Arrays of callbacks for each event name. */
        this.listeners = {};

        /** Array of callbacks for the `on()` call. */
        this.onCallbacks = [];
    }

    /**
     * Attach an event listener to an event.
     * @param {string} name
     * @param {string} callback
     */
    on(name, callback) {
        for (let callback of this.onCallbacks) {
            callback(name);
        }

        if (this.listeners[name] === undefined) {
            this.listeners[name] = [];
        }

        this.listeners[name].push(callback);
    }

    /**
     * Detach an event listener from an event.
     * @param {string} name
     * @param {string} callback
     */
    off(name, callback) {
        if (this.listeners[name]) {
            const index = this.listeners[name].indexOf(callback);
            if (index >= 0) {
                this.listeners[name].splice(index, 1);
            }
        }
    }

    /**
     * Attach an event listener to an event, but only once.
     * @param {string} name
     * @param {string} callback
     */
    once(name, callback) {
        const c = (...params) => {
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
    dispatch(name, ...params) {
        if (this.listeners[name]) {
            for (let callback of this.listeners[name]) {
                callback(...params);
            }
        }
    }
}

const WHISPER_EVENT_PREFIX = 'whisper-';

/**
 * ChannelEventListener class.
 * Allows to bind and unbind event callbacks including whisper events. Allows to trigger client events as well.
 */
class ChannelEventListener extends EventListener {

    /**
     * @param {Channel|PresenceChannel} channel
     */
    constructor(channel) {
        super();

        /**
         * Echo Channel instance
         * @type {Channel|PresenceChannel}
         */
        this.channel = channel;

        let boundEvents = {};

        // listen for each requested channel event and dispatch the result
        this.onCallbacks.push(name => {

            // only if not listening yet
            if (boundEvents[name] !== true) {

                //is whisper?
                if (name.startsWith(WHISPER_EVENT_PREFIX)) {

                    //remove the whisper prefix
                    name = name.substr(WHISPER_EVENT_PREFIX.length);

                    //dispatch on websocket event
                    channel.listenForWhisper(name, (...params) => {
                        this.dispatchWhisper(name, ...params);
                    });
                } else {

                    //dispatch on websocket event
                    channel.listen(name, (...params) => {
                        this.dispatch(name, ...params);
                    });
                }

                boundEvents[name] = true;
            }
        });
    }

    /**
     * Attach an event listener to a whisper event.
     * @param {string} name
     * @param {string} callback
     */
    onWhisper(name, callback) {
        this.on(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Detach an event listener from a whisper event.
     * @param {string} name
     * @param {string} callback
     */
    offWhisper(name, callback) {
        this.off(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Attach an event listener to a whisper event, but only once.
     * @param {string} name
     * @param {string} callback
     */
    onceWhisper(name, callback) {
        this.once(WHISPER_EVENT_PREFIX + name, callback);
    }

    /**
     * Dispatch a whisper event.
     * @param {string} name
     * @param params Parameters to pass to each callback.
     */
    dispatchWhisper(name, ...params) {
        this.dispatch(WHISPER_EVENT_PREFIX + name, ...params);
    }

    /**
     * Trigger a client event.
     * @param {string} name Event name
     * @param {object} data Event data
     */
    whisper(name, data) {
        this.channel.whisper(name, data);
    }
}

/**
 * Get channel instance by type and name
 * @param {Echo} echo
 * @param {string} type
 * @param {string} name
 * @return {Channel|PresenceChannel}
 */
function getChannelByType(echo, type, name) {
    switch (type) {
        case 'private':
            return echo.private(name);
        case 'presence':
            return echo.join(name);
        case 'public':
            return echo.channel(name);
        default:
            throw `Unknown channel type '${type}'.`;
    }
}

/**
 * ChannelListeners class.
 */
class ChannelListeners {

    /**
     * @param {Echo} echo Laravel Echo instance.
     */
    constructor(echo) {
        /**
         * Laravel Echo instance.
         * @type {Echo}
         */
        this.echo = echo;

        /** Internal array of event listeners per channel. */
        this._channelListeners = [];

        /**
         * Event listener for global events.
         * @type {EventListener}
         */
        this.global = new EventListener();
    }

    /**
     * Get an event listener for a particular channel.
     * @param {string} type 'private', 'presence' or 'public'.
     * @param {string} name Channel name.
     * @returns {ChannelEventListener}
     */
    channel(type, name) {
        // get the channel instance
        const channel = getChannelByType(this.echo, type, name);

        // retrieve from _channelListeners if already exists
        if (this._channelListeners[channel.name]) {
            return this._channelListeners[channel.name];
        }

        // create new event listener for channel
        const listener = new ChannelEventListener(channel);

        // add listener to _channelListeners
        this._channelListeners[channel.name] = listener;

        return listener;
    }
}

/*
 * Laravel Echo setup
 */
const channelListeners = new ChannelListeners(null);

(async () => {

    // wait for socket_host to be available
    await new Promise(resolve => {
        if (store.state.socket_host && store.state.token) {
            resolve();
            return;
        }

        store.watch(state => state.socket_host, value => {
            if (value) {
                resolve();
            }
        });
    });

    //instantiate Laravel Echo
    const echo = new Echo({
        broadcaster: 'socket.io',
        host: store.state.socket_host,
        csrfToken: store.state.token,
        client: io
    });

    // pass Laravel Echo instance to ChannelListeners
    channelListeners.echo = echo;

    //watch for socket.io errors and successes
    if (process.env.NODE_ENV === 'development') {
        echo.connector.socket.addEventListener('connect', () => {
            console.log('Socket.io successfully connected.')
        });

        echo.connector.socket.addEventListener('connect_error', e => {
            console.error('Socket.io connection error:', e);
        });

        echo.connector.socket.addEventListener('connect_timeout', e => {
            console.error('Socket.io connection timeout:', e);
        });

        echo.connector.socket.addEventListener('reconnect', () => {
            console.log('Socket.io successfully reconnected.')
        });

        echo.connector.socket.addEventListener('reconnecting', num => {
            console.log(`Socket.io's attempt to reconnect no. ${num}...`)
        });

        echo.connector.socket.addEventListener('reconnect_failed', e => {
            console.error('Socket.io failed to reconnect.', e);
        });
    }

    // dispatch 'reconnect' global event
    echo.connector.socket.addEventListener('reconnect', () => {
        channelListeners.global.dispatch('reconnect');
    });

    // watch for token changes and apply
    store.watch(state => state.token, value => echo.connector.options.csrfToken = value);

    /*
     * Set up global event listener events
     */

    function bindToGlobalEventListener(channel, events) {
        if (!Array.isArray(events)) {
            events = [events];
        }

        for (let event of events) {
            channel.listen(event, (...params) => {
                channelListeners.global.dispatch(event, ...params);
            });
        }
    }

    // user channel

    let userChannel = null;
    const userChannelName = user => `user.${user}`;

    function onUserChange(value, oldValue = null) {
        const username = value && value.username;
        const oldUsername = oldValue && oldValue.username;
        if (username !== oldUsername) {
            if (userChannel) {
                userChannel.unbind();
                userChannel = null;
            }

            if (username) {
                userChannel = echo.private(userChannelName(username));

                // listen for events and dispatch to global EventListener
                bindToGlobalEventListener(userChannel, [
                    'MessageSent'
                ]);
            }
        }
    }

    onUserChange(store.state.user);

    //set up user channel on user change
    store.watch(state => state.user, onUserChange);
})();

export default channelListeners;
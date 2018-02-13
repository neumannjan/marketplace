import io from 'socket.io-client';
import Echo from 'laravel-echo';
import store from 'JS/store';

/*
 * Instantiate Laravel Echo
 */
const echo = new Echo({
    broadcaster: 'socket.io',
    host: store.state.socket_host,
    csrfToken: store.state.token,
    client: io
});

/*
 * ListenerContainer class and instance
 */

class ListenerContainer {
    constructor() {
        this.listeners = {};
    }

    on(name, callback) {
        if (this.listeners[name] === undefined) {
            this.listeners[name] = [];
        }

        this.listeners[name].push(callback);
    }

    off(name, callback) {
        if (this.listeners[name]) {
            const index = this.listeners[name].indexOf(callback);
            if (index >= 0) {
                this.listeners[name].splice(index, 1);
            }
        }
    }

    once(name, callback) {
        const c = (...params) => {
            callback(...params);
            this.off(name, c);
        };

        this.on(name, c);
    }

    call(name, ...params) {
        if (this.listeners[name]) {
            for (let callback of this.listeners[name]) {
                callback(...params);
            }
        }
    }
}

const listenerContainer = new ListenerContainer();

/*
 * Laravel Echo setup
 */

// watch for token changes and apply
store.watch(state => state.token, value => echo.connector.options.csrfToken = value);

// user channel

let userChannel = null;
const userChannelName = user => `user.${user}`;

store.watch(state => state.user, (value, oldValue) => {
    const username = value && value.username;
    const oldUsername = oldValue && oldValue.username;
    console.log('watch called', username, oldUsername);
    if (username !== oldUsername) {
        if (userChannel) {
            userChannel.unbind();
            userChannel = null;

        }

        if (username) {
            console.log('echo called');
            userChannel = echo
                .private(userChannelName(username))
                .listen('MessageSent', message => {
                    console.log('MessageSent');
                    listenerContainer.call('MessageSent', message);
                });
        }
    }
});

export default listenerContainer;
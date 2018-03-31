import ConnectionManager, {ConnectionManagerEvents} from "JS/lib/echo";
import api from "JS/api";
import store from "JS/store";
import {ChannelType} from "JS/lib/echo/channel";
import {Message, User} from "JS/api/types";
import events, {Events} from "JS/events";
import { StoreWatcher } from "JS/echo/store-watcher";
import { normalizeMessage } from "JS/api/messaging/helpers";

const echo = new ConnectionManager();
const storeWatcher = new StoreWatcher(echo, store);


// Connection and reconnection
storeWatcher.onStoreState(['token', 'socket_host'], (payload) => {
    echo.connect(payload.socket_host.current, payload.token.current);
});


// Debug messages

if (process.env.NODE_ENV === 'development') {
    echo.on(ConnectionManagerEvents.Connect, () => {
        console.log('Socket.io successfully connected.');
    });

    echo.on(ConnectionManagerEvents.ConnectError, e => {
        console.error('Socket.io connection error:', e);
    });

    echo.on(ConnectionManagerEvents.ConnectTimeout, e => {
        console.error('Socket.io connection timeout:', e);
    });

    echo.on(ConnectionManagerEvents.Reconnect, () => {
        console.log('Socket.io successfully reconnected.');
    });

    echo.on(ConnectionManagerEvents.Reconnecting, num => {
        console.log(`Socket.io's attempt to reconnect no. ${num}...`);
    });

    echo.on(ConnectionManagerEvents.ReconnectFailed, e => {
        console.error('Socket.io failed to reconnect.', e);
    });

    echo.on(ConnectionManagerEvents.Disconnect, reason => {
        console.log(`Socket.io disconnected. Reason: ${reason}`);
    });
}


// connection detection

export function checkHttpConnection(force = false) {
    if (force || !store.state.connection_http) {
        api.requestSingle('dummy', {});
    }
}

echo.on(ConnectionManagerEvents.Connect, () => store.commit('websocketConnection', true));

echo.on(ConnectionManagerEvents.Reconnect, () => {
    store.commit('websocketConnection', true);
    checkHttpConnection();
});

echo.on(ConnectionManagerEvents.Disconnect, (reason: string) => {
    if (reason !== 'io client disconnect' && reason !== 'transport close') {
        store.commit('websocketConnection', false);
        checkHttpConnection(true);
    }
});


// user channel

const getUserChannelName = (userName: string) => `user.${userName}`;

storeWatcher.onStoreState('user', (payload) => {
    const username = payload.user.current && payload.user.current.username;
    const oldUsername = payload.user.old && payload.user.old.username;

    if (username !== oldUsername) {
        if (oldUsername) {
            echo.leave(getUserChannelName(oldUsername));
        }

        echo.reconnect();
        
        if (username) {
            const channel = echo.channel(ChannelType.Private, getUserChannelName(username), false);
            
            channel.on('MessageSent', (message: Message) => {
                events.dispatch(Events.MessageSent, normalizeMessage(message));
            });
        }
    }
});

export default echo;
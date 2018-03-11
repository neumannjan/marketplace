import ConnectionManager, {ConnectionManagerEvents} from "JS/lib/echo";
import api from "JS/api";
import store from "JS/store";
import {ChannelType} from "JS/lib/echo/channel";
import {Message} from "JS/api/types";
import events, {Events} from "JS/events";

const echo = new ConnectionManager();


// Connection and reconnection

function connect(host: string | null, token: string | null) {
    if(host && token) {
        echo.connect(host, token);
    }
}

connect(store.state.socket_host, store.state.token);
store.watch(state => state.token, token => connect(store.state.socket_host, token));
store.watch(state => state.socket_host, host => connect(host, store.state.token));


// Debug messages

if (process.env.NODE_ENV === 'development') {
    echo.on(ConnectionManagerEvents.Connect, () => {
        console.log('Socket.io successfully connected.')
    });

    echo.on(ConnectionManagerEvents.ConnectError, e => {
        console.error('Socket.io connection error:', e);
    });

    echo.on(ConnectionManagerEvents.ConnectTimeout, e => {
        console.error('Socket.io connection timeout:', e);
    });

    echo.on(ConnectionManagerEvents.Reconnect, () => {
        console.log('Socket.io successfully reconnected.')
    });

    echo.on(ConnectionManagerEvents.Reconnecting, num => {
        console.log(`Socket.io's attempt to reconnect no. ${num}...`)
    });

    echo.on(ConnectionManagerEvents.ReconnectFailed, e => {
        console.error('Socket.io failed to reconnect.', e);
    });

    echo.on(ConnectionManagerEvents.Disconnect, reason => {
        console.log(`Socket.io disconnected. Reason: ${reason}`)
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

store.watch(state => state.user, (user, oldUser) => {
    const username = user && user.username;
    const oldUsername = oldUser && oldUser.username;

    if (username !== oldUsername) {
        if (oldUsername) {
            echo.leave(getUserChannelName(oldUsername))
        }

        if (username) {
            const channel = echo.channel(ChannelType.Private, getUserChannelName(username));

            channel.on('MessageSent', (message: Message) => {
                events.dispatch(Events.MessageSent, message);
                if (message.mine === false || !store.state.user || store.state.user.username !== message.from.username) {
                    events.dispatch(Events.MessageSentOther, message);
                }
            });
        }
    }
});

export default echo;
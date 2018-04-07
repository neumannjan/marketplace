import {Connector} from './connector';
import {SocketIoChannel, SocketIoPresenceChannel, SocketIoPrivateChannel} from './../channel';

/**
 * This class creates a connnector to a Socket.io server.
 */
export declare class SocketIoConnector extends Connector {
    /**
     * The Socket.io connection instance.
     *
     * @type {object}
     */
    socket: any;
    /**
     * All of the subscribed channel names.
     *
     * @type {any}
     */
    channels: any;

    /**
     * Create a fresh Socket.io connection.
     *
     * @return void
     */
    connect(): void;

    /**
     * Get socket.io module from global scope or options.
     *
     * @type {object}
     */
    getSocketIO(): any;

    /**
     * Listen for an event on a channel instance.
     *
     * @param  {string} name
     * @param  {string} event
     * @param  {Function} callback
     * @return {SocketIoChannel}
     */
    listen(name: string, event: string, callback: Function): SocketIoChannel;

    /**
     * Get a channel instance by name.
     *
     * @param  {string} name
     * @return {SocketIoChannel}
     */
    channel(name: string): SocketIoChannel;

    /**
     * Get a private channel instance by name.
     *
     * @param  {string} name
     * @return {SocketIoChannel}
     */
    privateChannel(name: string): SocketIoPrivateChannel;

    /**
     * Get a presence channel instance by name.
     *
     * @param  {string} name
     * @return {SocketIoPresenceChannel}
     */
    presenceChannel(name: string): SocketIoPresenceChannel;

    /**
     * Leave the given channel.
     *
     * @param  {string} name
     * @return {void}
     */
    leave(name: string): void;

    /**
     * Get the socket ID for the connection.
     *
     * @return {string}
     */
    socketId(): string;

    /**
     * Disconnect Socketio connection.
     *
     * @return void
     */
    disconnect(): void;
}

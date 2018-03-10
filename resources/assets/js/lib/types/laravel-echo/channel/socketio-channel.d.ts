import { EventFormatter } from './../util';
import { Channel } from './channel';
/**
 * This class represents a Socket.io channel.
 */
export declare class SocketIoChannel extends Channel {
    /**
     * The Socket.io client instance.
     *
     * @type {any}
     */
    socket: any;
    /**
     * The name of the channel.
     *
     * @type {object}
     */
    name: any;
    /**
     * Channel options.
     *
     * @type {any}
     */
    options: any;
    /**
     * The event formatter.
     *
     * @type {EventFormatter}
     */
    eventFormatter: EventFormatter;
    /**
     * The event callbacks applied to the channel.
     *
     * @type {any}
     */
    events: any;
    /**
     * Create a new class instance.
     *
     * @param  {any} socket
     * @param  {string} name
     * @param  {any} options
     */
    constructor(socket: any, name: string, options: any);
    /**
     * Subscribe to a Socket.io channel.
     *
     * @return {object}
     */
    subscribe(): any;
    /**
     * Unsubscribe from channel and ubind event callbacks.
     *
     * @return {void}
     */
    unsubscribe(): void;
    /**
     * Listen for an event on the channel instance.
     *
     * @param  {string} event
     * @param  {Function} callback
     * @return {SocketIoChannel}
     */
    listen(event: string, callback: Function): SocketIoChannel;
    /**
     * Bind the channel's socket to an event and store the callback.
     *
     * @param  {string} event
     * @param  {Function} callback
     */
    on(event: string, callback: Function): void;
    /**
     * Attach a 'reconnect' listener and bind the event.
     */
    configureReconnector(): void;
    /**
     * Bind the channel's socket to an event and store the callback.
     *
     * @param  {string}   event
     * @param  {Function} callback
     * @return {void}
     */
    bind(event: string, callback: Function): void;
    /**
     * Unbind the channel's socket from all stored event callbacks.
     *
     * @return {void}
     */
    unbind(): void;
}

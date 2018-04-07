import {Connector} from './connector';
import {PresenceChannel, PusherChannel} from './../channel';

/**
 * This class creates a connector to Pusher.
 */
export declare class PusherConnector extends Connector {
    /**
     * The Pusher instance.
     *
     * @type {object}
     */
    pusher: any;
    /**
     * All of the subscribed channel names.
     *
     * @type {array}
     */
    channels: any;

    /**
     * Create a fresh Pusher connection.
     *
     * @return void
     */
    connect(): void;

    /**
     * Listen for an event on a channel instance.
     *
     * @param  {string} name
     * @param  {event} string
     * @param  {Function} callback
     * @return {PusherChannel}
     */
    listen(name: string, event: string, callback: Function): PusherChannel;

    /**
     * Get a channel instance by name.
     *
     * @param  {string} name
     * @return {PusherChannel}
     */
    channel(name: string): PusherChannel;

    /**
     * Get a private channel instance by name.
     *
     * @param  {string} name
     * @return {PusherPrivateChannel}
     */
    privateChannel(name: string): PusherChannel;

    /**
     * Get a presence channel instance by name.
     *
     * @param  {string} name
     * @return {PresenceChannel}
     */
    presenceChannel(name: string): PresenceChannel;

    /**
     * Leave the given channel.
     *
     * @param  {string} channel
     */
    leave(name: string): void;

    /**
     * Get the socket ID for the connection.
     *
     * @return {string}
     */
    socketId(): string;

    /**
     * Disconnect Pusher connection.
     *
     * @return void
     */
    disconnect(): void;
}

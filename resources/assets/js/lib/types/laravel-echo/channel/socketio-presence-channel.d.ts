import {PresenceChannel, SocketIoPrivateChannel} from './';

/**
 * This class represents a Socket.io presence channel.
 */
export declare class SocketIoPresenceChannel extends SocketIoPrivateChannel implements PresenceChannel {
    /**
     * Register a callback to be called anytime the member list changes.
     *
     * @param  {Function} callback
     * @return {object} SocketIoPresenceChannel
     */
    here(callback: Function): SocketIoPresenceChannel;

    /**
     * Listen for someone joining the channel.
     *
     * @param  {Function} callback
     * @return {SocketIoPresenceChannel}
     */
    joining(callback: Function): SocketIoPresenceChannel;

    /**
     * Listen for someone leaving the channel.
     *
     * @param  {Function}  callback
     * @return {SocketIoPresenceChannel}
     */
    leaving(callback: Function): SocketIoPresenceChannel;
}

import { SocketIoChannel } from './';
/**
 * This class represents a Socket.io presence channel.
 */
export declare class SocketIoPrivateChannel extends SocketIoChannel {
    /**
     * Trigger client event on the channel.
     *
     * @param  {string}  eventName
     * @param  {object}  data
     * @return {PusherPrivateChannel}
     */
    whisper(eventName: any, data: any): this;
}

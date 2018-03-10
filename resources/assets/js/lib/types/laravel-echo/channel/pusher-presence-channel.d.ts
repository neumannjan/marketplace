import { PusherChannel } from './pusher-channel';
import { PresenceChannel } from './presence-channel';
/**
 * This class represents a Pusher presence channel.
 */
export declare class PusherPresenceChannel extends PusherChannel implements PresenceChannel {
    /**
     * Register a callback to be called anytime the member list changes.
     *
     * @param  {Function} callback
     * @return {object} this
     */
    here(callback: any): PusherPresenceChannel;
    /**
     * Listen for someone joining the channel.
     *
     * @param  {Function} callback
     * @return {PusherPresenceChannel}
     */
    joining(callback: any): PusherPresenceChannel;
    /**
     * Listen for someone leaving the channel.
     *
     * @param  {Function}  callback
     * @return {PusherPresenceChannel}
     */
    leaving(callback: any): PusherPresenceChannel;
    /**
     * Trigger client event on the channel.
     *
     * @param  {Function}  callback
     * @return {PusherPresenceChannel}
     */
    whisper(eventName: any, data: any): PusherPresenceChannel;
}

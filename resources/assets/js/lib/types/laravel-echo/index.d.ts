import { Channel, PresenceChannel } from './channel';
/**
 * This class is the primary API for interacting with broadcasting.
 */
declare class Echo {
    /**
     * The broadcasting connector.
     *
     * @type {object}
     */
    connector: any;
    /**
     * The Echo options.
     *
     * @type {array}
     */
    options: any;
    /**
     * Create a new class instance.
     *
     * @param  {object} options
     */
    constructor(options: any);
    /**
     * Register a Vue HTTP interceptor to add the X-Socket-ID header.
     */
    registerVueRequestInterceptor(): void;
    /**
     * Register an Axios HTTP interceptor to add the X-Socket-ID header.
     */
    registerAxiosRequestInterceptor(): void;
    /**
     * Register jQuery AjaxSetup to add the X-Socket-ID header.
     */
    registerjQueryAjaxSetup(): void;
    /**
     * Listen for an event on a channel instance.
     */
    listen(channel: string, event: string, callback: Function): any;
    /**
     * Get a channel instance by name.
     *
     * @param  {string}  channel
     * @return {object}
     */
    channel(channel: string): Channel;
    /**
     * Get a private channel instance by name.
     *
     * @param  {string} channel
     * @return {object}
     */
    private(channel: string): Channel;
    /**
     * Get a presence channel instance by name.
     *
     * @param  {string} channel
     * @return {object}
     */
    join(channel: string): PresenceChannel;
    /**
     * Leave the given channel.
     *
     * @param  {string} channel
     */
    leave(channel: string): void;
    /**
     * Get the Socket ID for the connection.
     *
     * @return {string}
     */
    socketId(): string;
    /**
     * Disconnect from the Echo server.
     *
     * @return void
     */
    disconnect(): void;
}
export default Echo;

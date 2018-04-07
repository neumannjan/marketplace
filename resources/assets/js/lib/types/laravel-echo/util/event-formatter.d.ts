/**
 * Event name formatter
 */
export declare class EventFormatter {
    /**
     * Event namespace.
     *
     * @type {string}
     */
    namespace: string | boolean;

    /**
     * Create a new class instance.
     *
     * @params  {string | boolean} namespace
     */
    constructor(namespace: string | boolean);

    /**
     * Format the given event name.
     *
     * @param  {string} event
     * @return {string}
     */
    format(event: string): string;

    /**
     * Set the event namespace.
     *
     * @param  {string} value
     * @return {void}
     */
    setNamespace(value: string | boolean): void;
}

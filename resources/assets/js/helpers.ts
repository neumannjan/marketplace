export default {
    /**
     * Safely retrieves a value from a nested object. Returns defaultValue if the key is not present in the object.
     * @param {Object} object
     * @param {string | Array<string>} accessor Dot notation or an array of keys
     * @param defaultValue Value to be returned
     * @return Value or defaultValue
     */
    safeGet(object: Object, accessor: string | Array<string>, defaultValue: any = null): any {
        if (typeof accessor === 'string') {
            accessor = accessor.split('.');
        }

        return accessor.reduce(function (a: any, b: string) {
            if (a && (typeof a === 'object') && a[b])
                return a[b];
            else
                return null;
        }, object);
    },

    /**
     * Return a promise that resolves once an event is triggered.
     * @param {EventTarget} target
     * @param {string} type
     * @return {Promise}
     */
    awaitEvent(target: EventTarget, type: string): Promise<any> {
        return new Promise(resolve => {
            target.addEventListener(type, (event) => {
                resolve(event);
            }, {
                once: true
            });
        });
    },

    /**
     * Get a websocket conversation channel name based on user names.
     * @param {string} username1
     * @param {string} username2
     * @return {string}
     */
    getConversationChannelName(username1: string, username2: string): string {
        const name = 'conversation';
        if (username1 <= username2)
            return `${name}.${username1}.${username2}`;
        else
            return `${name}.${username2}.${username1}`;
    }

}
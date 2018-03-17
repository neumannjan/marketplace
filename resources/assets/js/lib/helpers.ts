/**
 * Safely retrieves a value from a nested object. Returns defaultValue if the key is not present in the object.
 * @param {object} object
 * @param {string | Array<string>} accessor Dot notation or an array of keys
 * @param defaultValue Value to be returned
 * @return Value or defaultValue
 */
export function safeGet(object: object, accessor: string | Array<string>, defaultValue: any = null): any {
    if (typeof accessor === 'string') {
        accessor = accessor.split('.');
    }

    return accessor.reduce(function (a: any, b: string) {
        if (a && (typeof a === 'object') && a[b])
            return a[b];
        else
            return null;
    }, object);
}

/**
 * Return a promise that resolves once an event is triggered.
 * @param {EventTarget} target
 * @param {string} type
 * @return {Promise<Event>}
 */
export function awaitEvent(target: EventTarget, type: string): Promise<any> {
    return new Promise((resolve: (event: Event) => void) => {
        target.addEventListener(type, event => {
            resolve(event);
        }, {
            once: true
        });
    });
}
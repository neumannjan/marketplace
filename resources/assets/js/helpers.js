export default {
    /**
     * Safely retrieves a value from a nested object. Returns defaultValue if the key is not present in the object.
     * @param {Object} object
     * @param {string|Array} accessor Dot notation or an array of keys
     * @param defaultValue Value to be returned
     */
    safeGet(object, accessor, defaultValue = null) {
        if (typeof accessor === 'string') {
            accessor = accessor.split('.');
        }

        return accessor.reduce(function (a, b) {
            if (a && (typeof a === 'object') && a[b])
                return a[b];
            else
                return null;
        }, object);
    },
}
/**
 * Object pool. Allows for reusing objects.
 */
export default class Pool {

    /**
     *
     * @param {function} newInstanceCallback Callback that instantiates a new object and returns it. It takes no parameters.
     * @param {function} prepareCallback Callback that takes an existing object as parameter and all parameters provided to the
     * `get` method. It should prepare the object before it is returned by the `get` method.
     * @param {Number} startAmount How many objects should be created at the beginning.
     */
    constructor(newInstanceCallback, prepareCallback, startAmount = 0) {
        this._new = newInstanceCallback;
        this._prepare = prepareCallback;

        this.array = [];
        this.amount = startAmount;

        for (let i = 0; i < startAmount; ++i)
            this.array.push(this._new());
    }

    /**
     * Retrieve an object, either new or reused.
     * @param params
     * @returns {*}
     */
    get(...params) {
        let instance;
        if (this.array.length > 0) {
            instance = this.array.pop();
        } else {
            instance = this._new();
            ++this.amount;
        }

        this._prepare(instance, ...params);
        return instance;
    }

    /**
     * Mark provided objects as unused and reusable.
     * @param objects
     */
    release(...objects) {
        this.array.push(...objects);
    }
}
/**
 * Object pool. Simplifies reusing objects.
 */
export default abstract class Pool<T, D extends object | Array<any>> {
    protected availableInstances: T[] = [];

    protected _createdAmount: number;

    /**
     * Instantiate a new object and return it.
     */
    protected abstract newInstance(): T;

    /**
     * Prepares an object instance before it is returned by the `get` method.
     */
    protected abstract prepareInstance(instance: T, definition: D): void;

    /**
     * @param {Number} startAmount Amount of objects that should be created at the beginning.
     */
    constructor(startAmount: number = 0) {
        this._createdAmount = startAmount;

        for (let i = 0; i < startAmount; ++i)
            this.availableInstances.push(this.newInstance());
    }

    /**
     * Retrieve an object, either new or reused.
     * @param {D} instanceDefinition
     * @returns {*}
     */
    get(instanceDefinition: D) {
        let instance: T;
        if (this.availableInstances.length > 0) {
            instance = <T>this.availableInstances.pop();
        } else {
            instance = this.newInstance();
            ++this._createdAmount;
        }

        this.prepareInstance(instance, instanceDefinition);
        return instance;
    }

    /**
     * Mark provided objects as unused and reusable.
     * @param {T[]} objects
     */
    release(...objects: T[]) {
        this.availableInstances.push(...objects);
    }
}
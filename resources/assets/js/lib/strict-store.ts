import {ActionTree, CommitOptions, DispatchOptions, GetterTree, MutationTree, Store} from "vuex";

interface PayloadWithType<T> {
    type: keyof T;
    [index: string]: any;
}

export declare interface StrictStore<
    State,
    M extends MutationTree<State>,
    A extends ActionTree<State, State>,
    G extends GetterTree<State, State>>
    extends Store<State> {

    readonly getters: {
        [index in keyof G]: any
    };

    dispatch: {
        (type: keyof A, payload?: any, options?: DispatchOptions): Promise<any>,
        (payloadWithType: PayloadWithType<A>, options?: DispatchOptions): Promise<any>;
    };

    commit: {
        (type: keyof M, payload?: any, options?: CommitOptions): void,
        (payloadWithType: PayloadWithType<M>, options?: CommitOptions): void;
    };
}
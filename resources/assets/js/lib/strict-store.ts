import {ActionTree, CommitOptions, DispatchOptions, GetterTree, MutationTree, Store} from "vuex";

interface PayloadWithType<T> {
    type: keyof T;

    [index: string]: any;
}

type PayloadType<T> =
    T extends (a: any, payload: infer P) => any ? P :
        T extends { root?: boolean; handler: (a: any, payload: infer P) => any } ? P :
            never;

export declare interface StrictStore<State,
    M extends MutationTree<State>,
    A extends ActionTree<State, State>,
    G extends GetterTree<State, State>>
    extends Store<State> {

    readonly getters: {
        [index in keyof G]: ReturnType<G[index]>
    };

    dispatch: {
        <T extends keyof A>(type: T, payload?: PayloadType<A[T]>, options?: DispatchOptions): Promise<any>,
        (payloadWithType: PayloadWithType<A>, options?: DispatchOptions): Promise<any>;
    };

    commit: {
        <T extends keyof M>(type: T, payload?: PayloadType<M[T]>, options?: CommitOptions): void,
        (payloadWithType: PayloadWithType<M>, options?: CommitOptions): void;
    };
}
declare module "vuex-persistedstate" {
    import {MutationPayload, Store} from "vuex";

    export interface State {
        [name: string]: any
    }

    export default function <State extends { [index: string]: any }>(options: {
        key?: string,
        paths?: Array<keyof State>,
        reducer?: (state: State, paths: string[]) => any,
        subscriber?: (store: Store<any>) => ((handler: (mutation: MutationPayload, state: State) => void) => void),
        storage?: Storage,
        getState?: (key: string, storage: Storage) => any,
        setState?: (key: string, state: State, storage: Storage) => any,
        filter?: (mutation: MutationPayload) => boolean,
    }, storage?: Storage, key?: string): (store: Store<any>) => void;
}
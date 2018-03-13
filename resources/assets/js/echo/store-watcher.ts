import ConnectionManager from 'JS/lib/echo';
import { Store } from 'vuex';
import { StrictStore } from 'JS/lib/strict-store';
import { State } from 'vuex-persistedstate';

type Payload<State, T extends keyof State> = {
    [key in T]: {
        current: State[key],
        old?: State[key]
    }
}

export class StoreWatcher<State = {[index: string]: any}> {
    protected echo: ConnectionManager;
    protected store: Store<State> | StrictStore<State, any, any, any>;

    constructor(echo: ConnectionManager, store: Store<State> | StrictStore<State, any, any, any>) {
        this.echo = echo;
        this.store = store;
    }

    onStoreState<T extends keyof State>(watching: Array<T> | T, listener: (payload: Payload<State, T>) => void): void {
        if(!Array.isArray(watching)) {
            watching = [watching];
        }

        const retrieveData = (): Payload<State, T> => {
            let data = {} as Payload<State, T>;
            for(let key of watching) {
                data[<T>key] = {
                    current: this.store.state[<T>key]
                };
            }

            return data;
        }

        listener(retrieveData());

        for(let key of watching) {
            this.store.watch(state => state[key], (currentValue: any, oldValue: any) => {
                let data = retrieveData();
                data[key].old = oldValue;

                listener(data);
            });
        }
    }
}
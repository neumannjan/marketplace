interface InitialState {
    [index: string]: any
}

export default class InitialStore<State extends InitialState> {
    public readonly state: State;

    constructor(state: State) {
        this.state = state;
    }

    get<T extends keyof State>(index: T, instead: State[T]): State[T] {
        if (this.state[index] !== undefined) {
            return this.state[index];
        } else {
            return instead;
        }
    }
}
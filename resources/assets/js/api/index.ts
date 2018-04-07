import BaseApi from "JS/lib/api";
import store from "JS/store";
import {GlobalResponse} from "JS/api/types";

/**
 * API class that supports the app's store instance
 */
export class Api extends BaseApi {

    protected onConnection(has: boolean): void {
        store.commit('httpConnection', has);
    }

    protected getCsrfToken(): string | null {
        return store.state.token;
    }

    protected onGlobalResponse(data: GlobalResponse): void {
        store.commit('global', data);
    }
}

export default new Api();
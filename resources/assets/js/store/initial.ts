import InitialStore from "JS/lib/initial-store";
import {InitialResponse} from "JS/api/types";

const initialData = window.data ? (<InitialResponse> JSON.parse(atob(window.data))) : null;

if (!initialData) {
    throw 'Missing data required to initialize the app!';
}

export default new InitialStore<InitialResponse>(initialData);
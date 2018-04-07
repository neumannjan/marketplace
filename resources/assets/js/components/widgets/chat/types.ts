import {MessageAdditional} from "JS/api/types";

export interface MessageSender {
    (content: string, additional?: MessageAdditional, additionalPrivate?: MessageAdditional): void
}
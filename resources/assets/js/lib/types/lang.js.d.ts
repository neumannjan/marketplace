declare module 'lang.js' {
    export interface TranslationMessages {
        [key: string]: string | TranslationMessages
    }

    export interface TranslationReplacements {
        [variable: string]: string
    }

    export interface TranslationOptions {
        locale?: string,
        fallback?: string,
        messages?: TranslationMessages
    }

    export default class {
        constructor(options?: TranslationOptions);
        setMessages(messages: TranslationMessages): void;
        getLocale(): string;
        setLocale(locale: string): void;
        getFallback(): string;
        setFallback(fallback: string): void;
        has(key: string, locale?: string): boolean;
        get(key: string, replacements?: TranslationReplacements, locale?: string): string;
        choice(key: string, number: number, replacements?: TranslationReplacements, locale?: string): string;
    }
}
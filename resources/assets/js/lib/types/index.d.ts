// Node.js
declare let process: {
    env: {
        NODE_ENV: 'production' | 'development'
        [key: string]: string | undefined
    }
};

// Vue
declare module "*.vue" {
    import Vue from 'vue';
    export default Vue
}

// CSS Element Queries
declare module "css-element-queries/src/ElementQueries" {
    function listen(): void;
}

declare module "css-element-queries/src/ResizeSensor" {
    class ResizeSensor {
        constructor(element: Element | Element[], callback: Function);
        static detach(element: Element | Element[], callback: Function): void;
    }

    export default ResizeSensor;
}

// Laravel Echo
declare module "laravel-echo" {
    import Echo from "JS/lib/types/laravel-echo";
    export default Echo;
}
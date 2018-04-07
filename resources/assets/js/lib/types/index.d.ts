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

// Bootstrap Vue
declare module "bootstrap-vue/src/components/*" {
    import Vue from 'vue';
    export default Vue;
}

// Vue class component
declare module "vue-property-decorators" {
    import {NavigationGuard} from 'vue-router';

    class Vue {
        beforeRouteEnter?: NavigationGuard;
        beforeRouteLeave?: NavigationGuard;
        beforeRouteUpdate?: NavigationGuard;

        beforeCreate?(this: ThisType<Vue>): void;

        created?(): void;

        beforeDestroy?(): void;

        destroyed?(): void;

        beforeMount?(): void;

        mounted?(): void;

        beforeUpdate?(): void;

        updated?(): void;

        activated?(): void;

        deactivated?(): void;

        errorCaptured?(): boolean | void;
    }
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
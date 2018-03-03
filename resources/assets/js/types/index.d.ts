// Vue

declare module "*.vue" {
    import Vue from 'vue'
    export default Vue
}

// CSS Element Queries

declare module "css-element-queries/src/ElementQueries" {
    function listen(): void;
}

declare module "css-element-queries" {

}
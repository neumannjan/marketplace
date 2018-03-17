import Component from 'vue-class-component';

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteLeave',
    'beforeRouteUpdate',
]);

export default Component;
export { createDecorator, VueDecorator, mixins } from 'vue-class-component';
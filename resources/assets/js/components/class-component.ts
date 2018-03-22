export { Component, Watch, Prop, Vue } from 'vue-property-decorator';
import { Component } from 'vue-property-decorator';

Component.registerHooks([
    'beforeRouteEnter',
    'beforeRouteLeave',
    'beforeRouteUpdate',
]);

export default Component;
export { createDecorator, VueDecorator, mixins } from 'vue-class-component';
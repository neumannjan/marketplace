///<reference path="../../lib/types/index.d.ts" />

import ResizeSensor from 'css-element-queries/src/ResizeSensor';
import echo from 'JS/echo';
import Vue from "vue";
import _EventListener from "JS/lib/event-listener";
import {ChannelType} from "JS/lib/echo/channel";
import Component from "vue-class-component";

interface Function {
    (...params: any[]): void
}

@Component({})
export default class EventsMixin extends Vue {
    events: [Function, Function][] = [];
    vueActive: boolean = true;

    $attachEvent(add: Function, remove: Function) {
        if (this.vueActive)
            add();

        this.events.push([add, remove]);
    }

    $onJS(target: EventTarget, event: string, listener: EventListenerOrEventListenerObject) {
        this.$attachEvent(
            () => target.addEventListener(event, listener),
            () => target.removeEventListener(event, listener)
        );
    }

    $onElResize(el: Element, listener: Function) {
        this.$attachEvent(
            () => el ? new ResizeSensor(el, listener) : false,
            () => el ? ResizeSensor.detach(el, listener) : false
        );
    }

    $onVue(vm: Vue, event: string, listener: Function) {
        this.$attachEvent(
            () => vm.$on(event, listener),
            () => vm.$off(event, listener)
        );
    }

    $onEventListener<
        EventListener extends _EventListener<string | number> = _EventListener<string | number>,
        Names extends string | number = string | number>
    (eventListener: EventListener, event: Names, listener: Function) {
        this.$attachEvent(
            () => eventListener.on(event, listener),
            () => eventListener.off(event, listener),
        );
    }

    $onEcho(type: ChannelType, channel: string, event: string, listener: Function) {
        const channelListener = echo.channel(type, channel);
        this.$onEventListener(channelListener, event, listener);
    }

    $onEchoWhisper(type: ChannelType, channel: string, event: string, listener: Function) {
        const channelListener = echo.channel(type, channel);
        this.$attachEvent(
            () => channelListener.onWhisper(event, listener),
            () => channelListener.offWhisper(event, listener)
        );
    }

    $onDocumentVisibility(listener: (hidden: boolean) => void) {
        const listen = () => listener(document.hidden);

        this.$attachEvent(
            () => {
                listen();
                return document.addEventListener('visibilitychange', listen);
            },
            () => document.removeEventListener('visibilitychange', listen)
        );
    }

    activated() {
        this.vueActive = true;
        for (let [add] of this.events)
            add();
    }

    deactivated() {
        this.vueActive = false;
        for (let [add, remove] of this.events)
            remove();
    }

    destroyed() {
        this.vueActive = false;
        for (let [add, remove] of this.events)
            remove();
        this.events = [];
    }
}
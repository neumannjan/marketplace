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

export interface EventsMixinInterface {
    /**
     * Bind an event to this Vue instance
     * @param add {Function} Callback that attaches the event
     * @param remove {Function} Callback that detaches the event
     */
    $attachEvent(add: Function, remove: Function): void;

    /**
     * Bind an EventTarget event to this Vue instance.
     * @param target {EventTarget} The target of the event
     * @param event {string} Event name
     * @param listener {EventListenerOrEventListenerObject}
     */
    $onJS(target: EventTarget, event: string, listener: EventListenerOrEventListenerObject): void;

    /**
     * Bind an element resize event to this Vue instance.
     * @param el {Element} The element to await resize of
     * @param listener {Function}
     */
    $onElResize(el: Element, listener: Function): void;

    /**
     * Bind an event of another Vue instance to this Vue instance.
     * @param vm {Vue} The other Vue instace
     * @param event {string} Event name
     * @param listener {Function}
     */
    $onVue(vm: Vue, event: string, listener: Function): void;

    /**
     * Bind an EventListener event to this Vue instance.
     * @param eventListener {EventListener} The event listener
     * @param event {Names} The event name/type
     * @param listener {Function}
     */
    $onEventListener<
        EventListener extends _EventListener<string | number> = _EventListener<string | number>,
        Names extends string | number = string | number>
    (eventListener: EventListener, event: Names, listener: Function): void;

    /**
     * Bind an Echo event to this Vue instance.
     * @param type {ChannelType} Channel type
     * @param channel {string} Channel name
     * @param event {string} Event name
     * @param listener {Function}
     */
    $onEcho(type: ChannelType, channel: string, event: string, listener: Function): void;

    /**
     * Bind an Echo whisper event to this Vue instance.
     * @param type {ChannelType} Channel type
     * @param channel {string} Channel name
     * @param event {string} Event name
     * @param listener {Function}
     */
    $onEchoWhisper(type: ChannelType, channel: string, event: string, listener: Function): void;

    /**
     * Bind a document visibility change event to this Vue instance.
     * @param listener {Function}
     */
    $onDocumentVisibility(listener: (hidden: boolean) => void): void;
}

@Component({})
export default class EventsMixin extends Vue implements EventsMixinInterface {
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
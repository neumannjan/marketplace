import Pool from 'JS/lib/pool';

type CanvasPoolDefinition = [number, number];

/**
 * Pool for HTMLCanvasElement instances
 */
export default class CanvasPool extends Pool<HTMLCanvasElement, CanvasPoolDefinition> {

    protected newInstance(): HTMLCanvasElement {
        return document.createElement('canvas');
    }

    protected prepareInstance(instance: HTMLCanvasElement, definition: CanvasPoolDefinition): void {
        const [width, height] = definition;
        instance.width = width;
        instance.height = height;
    }
}
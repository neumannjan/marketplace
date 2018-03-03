interface HTMLImageElement extends ImageBitmap {
}

/**
 * Safari and Edge polyfill for createImageBitmap
 * https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope/createImageBitmap
 */
if (!('createImageBitmap' in window)) {
    (<Window> window).createImageBitmap =
        async function (image: HTMLImageElement | SVGImageElement | HTMLVideoElement | HTMLCanvasElement | ImageBitmap | ImageData | Blob) {
            return new Promise((resolve: (value: HTMLImageElement) => void) => {
                let img = document.createElement('img');
                img.addEventListener('load', function () {
                    resolve(img);
                });
                img.src = URL.createObjectURL(image);
            });
        };
}
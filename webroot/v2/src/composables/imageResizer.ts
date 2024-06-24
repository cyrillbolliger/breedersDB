/* eslint-disable */
// @ts-nocheck
// the currently used packages don't know OffscreenCanvas yet.
// TODO: remove ts-nocheck and eslint-disable when the packages are updated

export function resizeImageFile(file: File, maxWidth: number, maxHeight: number, quality: number, type: string): Promise<File> {
  if (typeof OffscreenCanvas === "undefined") {
    return Promise.reject(new Error('OffscreenCanvas is not supported'));
  }

  return new Promise((resolve, reject) => {
    const img = new Image();

    img.onload = () => {
      let width = img.width;
      let height = img.height;

      if (width > height) {
        if (width > maxWidth) {
          height *= maxWidth / width;
          width = maxWidth;
        }
      } else {
        if (height > maxHeight) {
          width *= maxHeight / height;
          height = maxHeight;
        }
      }

      const canvas = new OffscreenCanvas(width, height);
      const ctx = canvas.getContext('2d');

      if (!ctx) {
        return reject(new Error('OffscreenCanvas failed to get context'));
      }

      ctx.drawImage(img, 0, 0, width, height);

      canvas.convertToBlob({type, quality}).then((blob) => {
        resolve(new File([blob], file.name, {type}));
      });
    };

    img.src = URL.createObjectURL(file);
  });
}

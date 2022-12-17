export default function useTextWidth() {
  let canvas: HTMLCanvasElement;

  function getFontProps(el: HTMLElement) {
    const fontWeight = getCssStyle(el, 'font-weight') || 'normal';
    const fontSize = getCssStyle(el, 'font-size') || '16px';
    const fontFamily = getCssStyle(el, 'font-family') || 'Times New Roman';

    return `${fontWeight} ${fontSize} ${fontFamily}`;
  }

  function getTextWidth(text: string, fontProps: string) {
    canvas = canvas || document.createElement('canvas');
    const context = canvas.getContext('2d');

    if (!context) {
      return undefined;
    }

    context.font = fontProps;
    return context.measureText(text).width;
  }

  function getCssStyle(element: HTMLElement, prop: string) {
    return window.getComputedStyle(element).getPropertyValue(prop);
  }

  return {
    getTextWidth,
    getFontProps,
  }
}

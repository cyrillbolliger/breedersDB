<template>
  <div ref="scanPreviewZoomBox" style="opacity: 0; width: calc(100vw - 32px)">
    <div
      ref="scanPreview"
      id="scanPreview"
      :style="`width: ${readerSize.width}px; height: ${readerSize.height}px;`"
    >
      <video ref="scanVideo"/>
      <canvas/>
    </div>
  </div>
</template>

<script lang="ts">
import {defineComponent, onBeforeUnmount, onMounted, PropType, ref} from 'vue';
import Quagga, {
  QuaggaJSConfigObject,
  QuaggaJSReaderConfig,
  QuaggaJSResultObject,
  QuaggaJSResultObject_CodeResult
} from '@ericblade/quagga2';

const onProcessed = function(result: QuaggaJSResultObject) {
  let drawingCtx = Quagga.canvas.ctx.overlay;
  let drawingCanvas = Quagga.canvas.dom.overlay;
  if (result) {
    if (result.boxes) {
      drawingCtx.clearRect(
        0,
        0,
        parseInt(drawingCanvas.getAttribute('width') || ''),
        parseInt(drawingCanvas.getAttribute('height') || '')
      );
      result.boxes
        .filter(function (box) {
          return box !== result.box;
        })
        .forEach(function (box) {
          Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {
            color: '#ff0000',
            lineWidth: 2,
          });
        });
    }
    if (result.box) {
      Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {
        color: '#00ff00',
        lineWidth: 2,
      });
    }
    if (result.codeResult && result.codeResult.code) {
      Quagga.ImageDebug.drawPath(
        result.line,
        {x: 'x', y: 'y'},
        drawingCtx,
        {color: '#ffbf00', lineWidth: 3}
      );
    }
  }
}

const median = function(numbers: number[]) {
  const sorted = numbers.slice().sort((a, b) => a - b);
  const middle = Math.floor(sorted.length / 2);

  if (sorted.length % 2 === 0) {
    return (sorted[middle - 1] + sorted[middle]) / 2;
  }

  return sorted[middle];
}

// see https://github.com/serratus/quaggaJS/issues/237#issuecomment-742878333
const isValid = function(result: QuaggaJSResultObject_CodeResult) {
  const errors: number[] = result.decodedCodes
    .filter(
      (code): code is {error: number, code: number, start: number, end: number} =>
        code.error !== undefined
    ).map(code => code.error);

  const error = median(errors);

  // Good result for code_128 : median <= 0.08 and maxError < 0.1
  return !(error > 0.08 || errors.some(err => err > 0.1))
}

export default defineComponent({
  name: 'BarcodeScanner',
  emits: ['onDetected'],

  props: {
    readerTypes: {
      type: Array as PropType<(QuaggaJSReaderConfig | string)[]>,
      default: () => ['code_128_reader'],
    },

    readerSize: {
      type: Object as PropType<{width: number, height: number}>,
      default: () => ({
        width: 640,
        height: 480,
      })
    },

    aspectRatio: {
      type: Object as PropType<ConstrainDouble>,
      default: () => ({
        min: 1,
        max: 2,
      })
    },

    facingMode: {
      type: String as PropType<ConstrainDOMString>,
      default: () => 'environment'
    }
  },

  setup(props, {emit}) {

    const scanPreviewZoomBox = ref<HTMLDivElement|null>(null)
    const scanPreview = ref<HTMLDivElement|null>(null)
    const scanVideo = ref<HTMLVideoElement|null>(null)

    const quaggaConfig: QuaggaJSConfigObject = {
      inputStream: {
        type: 'LiveStream',
        constraints: {
          width: {ideal: props.readerSize.width},
          height: {ideal: props.readerSize.height},
          facingMode: props.facingMode,
          // aspectRatio: {min: 1, max: 2},
        },
        target: '#scanPreview',
      },
      frequency: 10,
      locator: {
        patchSize: 'medium',
        halfSample: true,
      },
      decoder: {
        readers: props.readerTypes,
      },
      locate: true,
    }

    onMounted(() => {
      void Quagga.init(quaggaConfig, function (err) {
        if (err) {
          return console.error(err);
        }
        setPreviewSize()
        Quagga.start();
      });
      Quagga.onDetected(onDetected);
      Quagga.onProcessed(onProcessed);
    })

    onBeforeUnmount(() => {
      Quagga.offDetected(onDetected);
      Quagga.offProcessed(onProcessed);
      void Quagga.stop();
    })

    function onDetected(result: QuaggaJSResultObject) {
      const codeResult = result.codeResult
      if (isValid(codeResult)) {
        emit('onDetected', codeResult.code)
      }
    }

    function setPreviewSize() {
      const zoomEl = scanPreviewZoomBox.value
      const videoEl = scanVideo.value

      if (!zoomEl || !videoEl) {
        return
      }

      const availableWidth = parseInt(getComputedStyle(zoomEl).width)

      const videoElStyles = getComputedStyle(videoEl)
      const usedWidth = parseInt(videoElStyles.width)
      const usedHeight = parseInt(videoElStyles.height)

      if (availableWidth > usedWidth) {
        return
      }

      const zoom = availableWidth / usedWidth

      zoomEl.style.transform = `scale(${zoom})`
      zoomEl.style.transformOrigin = 'left top';
      zoomEl.style.height = `${usedHeight * zoom}px`;
      zoomEl.style.opacity = '1';
    }


    return {
      scanPreviewZoomBox,
      scanPreview,
      scanVideo
    }
  },
})

</script>

<style scoped>
div {
  position: relative;
}

div canvas,
div video {
  position: absolute;
  left: 0;
  top: 0;
}
</style>

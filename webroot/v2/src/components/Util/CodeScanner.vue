<template>
  <div class="q-pa-md" v-if="!videoAccess || loading">
    <div v-if="!videoAccess">{{ t('components.util.codeScanner.permissionRequest') }}</div>
    <div v-if="videoAccess && loading">{{ t('components.util.codeScanner.loadingMessage') }}</div>
  </div>
  <canvas id="canvas" ref="canvasElement" :hidden="loading"/>
</template>

<script lang="ts">
import {defineComponent, ref, watch, onMounted, onBeforeUnmount} from 'vue';
import jsQR from 'jsqr';
import {useI18n} from 'vue-i18n';
import {Point} from 'jsqr/dist/locator';

const frameColor = '#FF3B58';

export default defineComponent({
  name: 'CodeReader',
  emits: ['onDetected', 'onReady'],
  props: {},
  setup(_, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const video = document.createElement('video');
    const videoAccess = ref(false);
    const loading = ref(true);
    let videoStream: MediaStream;

    const canvasElement = ref<HTMLCanvasElement | null>(null);
    let canvas: CanvasRenderingContext2D;

    onMounted(() => {
      let context = canvasElement.value?.getContext('2d');
      if (context) {
        canvas = context;
      }
      initVideo();
    });

    onBeforeUnmount(() => {
      if (videoStream) {
        videoStream.getTracks().forEach(function (track) {
          track.stop();
        });
      }
    });

    function drawLine(begin: Point, end: Point, color: string) {
      canvas.beginPath();
      canvas.moveTo(begin.x, begin.y);
      canvas.lineTo(end.x, end.y);
      canvas.lineWidth = 4;
      canvas.strokeStyle = color;
      canvas.stroke();
    }

    function initVideo() {
      // Use facingMode: environment to attempt to get the front camera on phones
      void navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}})
        .then(function (stream) {
          videoStream = stream;
          video.srcObject = stream;
          video.setAttribute('playsinline', ''); // required to tell iOS safari we don't want fullscreen
          void video.play();
          videoAccess.value = true
          requestAnimationFrame(tick);
        });
    }

    function tick() {
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        loading.value = false;
        let el: HTMLCanvasElement;

        if ( ! canvasElement.value) {
          requestAnimationFrame(tick);
          return;
        } else {
          el = canvasElement.value;
        }

        el.height = video.videoHeight;
        el.width = video.videoWidth;
        canvas.drawImage(video, 0, 0, el.width, el.height);
        const imageData = canvas.getImageData(0, 0, el.width, el.height);
        const code = jsQR(
          imageData.data,
          imageData.width,
          imageData.height,
          {
            inversionAttempts: 'dontInvert',
          });

        if (code) {
          drawLine(code.location.topLeftCorner, code.location.topRightCorner, frameColor);
          drawLine(code.location.topRightCorner, code.location.bottomRightCorner, frameColor);
          drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, frameColor);
          drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, frameColor);
          emit('onDetected', code.data)
        }
      }
      requestAnimationFrame(tick);
    }

    watch(loading, val => {
      if (!val) {
        emit('onReady')
      }
    });

    return {
      t,
      canvasElement,
      videoAccess,
      loading
    }
  }
});
</script>


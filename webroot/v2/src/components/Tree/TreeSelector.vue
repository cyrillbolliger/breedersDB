<template>
  <div class="q-gutter-md">

    <!--suppress RequiredAttributes -->
    <q-btn-toggle
      v-model="mode"
      toggle-color="primary"
      size="sm"
      :options="[
        {label: t('marks.selectTree.scanQrCode'), value: InputMode.Camera, icon: 'qr_code_scanner'},
        {label: t('marks.selectTree.manualEntry'), value: InputMode.Keyboard, icon: 'keyboard'},
      ]"
    />

    <!--suppress RequiredAttributes -->
    <q-input
      v-if="mode === InputMode.Keyboard"
      outlined
      v-model="publicid"
      :label="t('trees.publicid')"
      @keyup.enter="loadTree"
      type="number"
      inputmode="numeric"
      :autofocus="true"
    />

    <div
      v-if="mode === InputMode.Camera"
      class="q-mb-md row justify-center bg-grey-5"
      :class="{loading}"
    >
      <CodeScanner
        @on-detected="onScanned"
        @on-ready="onCodeScannerReady"
      />
    </div>

    <q-btn
      color="primary"
      :label="t('general.next')"
      :disabled="!publicid"
      @click="loadTree"
    />

    <SpinLoader v-if="loading"/>
  </div>
</template>

<script lang="ts">
import {defineComponent, ref, computed, watch, onMounted} from 'vue'
import {useI18n} from 'vue-i18n';
import SpinLoader from 'components/Util/SpinLoader.vue';
import {Tree} from 'src/models/tree';
import useApi from 'src/composables/api'
import CodeScanner from 'components/Util/CodeScanner.vue';
import {Notify} from 'quasar';

const localStorageInputMethod = 'breedersdb_mark_input_method';

enum InputMode {
  Camera = 'CAMERA',
  Keyboard = 'KEYBOARD',
}

export default defineComponent({
  name: 'TreeSelector',
  emits: ['selected'],
  components: {CodeScanner, SpinLoader},

  setup(_, {emit}) {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const {working: apiLoading, get} = useApi()

    const scannerLoading = ref(true)
    const publicid = ref('')

    const loading = computed(() => apiLoading.value || scannerLoading.value)

    const mode = ref<InputMode>(window.localStorage.getItem(localStorageInputMethod) === InputMode.Keyboard
      ? InputMode.Keyboard
      : InputMode.Camera
    )

    watch(mode, val => {
      window.localStorage.setItem(localStorageInputMethod, val)
      scannerLoading.value = InputMode.Camera === getInputMode()
    })

    onMounted(() => {
      if (InputMode.Keyboard === getInputMode()) {
        scannerLoading.value = false
      }
    })

    function getInputMode() {
      return mode.value;
    }

    function loadTree() {
      const params = new URLSearchParams()
      params.append('fields[]', 'publicid')
      params.append('term', publicid.value)
      const url = 'trees/get-tree?' + params.toString()

      void get<Tree>(url)
        .then(tree => {
          if (tree) {
            emit('selected', tree)
          } else {
            Notify.create({
              message: t('general.failedToLoadData'),
              color: 'negative',
              closeBtn: true
            })
          }
        })
    }

    function onScanned(code: string) {
      publicid.value = code
      loadTree()
    }

    function onCodeScannerReady() {
      scannerLoading.value = false
    }

    return {
      t,
      publicid,
      loadTree,
      loading,
      onScanned,
      onCodeScannerReady,
      mode,
      InputMode,
    }
  }
})
</script>

<style scoped lang="scss">
.loading {
  filter: grayscale(1) opacity(0.2) blur(4px);
  transition: all 200ms;
}
</style>

<template>
  <q-btn-toggle
    v-model="inputMethod"
    :options="options"
    size="sm"
    toggle-color="primary"
  />
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, onMounted, ref, watch} from 'vue';

const localStorageInputMethod = 'breedersdb_mark_input_method';

type InputMethod = 'SEARCH' | 'CAMERA' | 'KEYBOARD'

const emit = defineEmits<{ (e: 'change', value: InputMethod): void }>()
const props = defineProps({
  searchSelectVariety: {
    type: Boolean,
    default: false,
  },
  qrScanTree: {
    type: Boolean,
    default: false,
  },
  inputPublicidTree: {
    type: Boolean,
    default: false,
  },
})

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const options = computed(() => {
  const o: { label: string, value: InputMethod, icon: string }[] = [];
  if (props.searchSelectVariety)
    o.push({label: t('marks.selectVariety.searchSelect'), value: 'SEARCH', icon: 'search'});
  if (props.qrScanTree)
    o.push({label: t('marks.selectTree.scanQrCode'), value: 'CAMERA', icon: 'qr_code_scanner'});
  if (props.inputPublicidTree)
    o.push({label: t('marks.selectTree.manualEntry'), value: 'KEYBOARD', icon: 'keyboard'});
  return o
})

const getDefaultInputMethod = () => {
  const storedMethod = window.localStorage.getItem(localStorageInputMethod) || ''
  const allowedMethods = options.value.map(o => o.value)
  return allowedMethods.find(method => method === storedMethod) || allowedMethods[0]
}

const inputMethod = ref(getDefaultInputMethod())

watch(inputMethod, val => {
  window.localStorage.setItem(localStorageInputMethod, val)
  emit('change', val)
})

onMounted(() => emit('change', inputMethod.value))
</script>

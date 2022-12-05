<template>
  <q-input
    :borderless="!hasFocus && !hasChanged"
    :outlined="hasFocus || hasChanged"
    :modelValue="modelValue"
    :placeholder="t('queries.unsaved')"
    autocomplete="off"
    maxlength="120"
    type="text"
    @update:model-value="change"
    @focus="hasFocus = true"
    @blur="hasFocus = false"
    :loading="loading"
    :debounce="250"
    :error="false === uniqueName"
    :error-message="t('queries.titleNotUnique')"
  >
  </q-input>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {ref} from 'vue';
import useApi from 'src/composables/api';

defineProps({
  modelValue: String,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const api = useApi();

const hasFocus = ref(false);
const loading = ref(false);
const hasChanged = ref(false);
const uniqueName = ref<boolean|null>(null);

function change(val: string) {
  void checkCode(val);
  hasChanged.value = true;
  emit('update:modelValue', val)
}

async function checkCode(code: string) {
  loading.value = true;

  try{
    await api.get(`queries/viewByCode/${code}`, () => null, {}, false)
    uniqueName.value = false;
  } catch {
    uniqueName.value = true;
  }

  loading.value = false;
}

</script>

<style scoped>
/*noinspection CssUnusedSymbol*/
.q-input {
  max-width: 280px;
  font-size: 1.5rem;
}
</style>

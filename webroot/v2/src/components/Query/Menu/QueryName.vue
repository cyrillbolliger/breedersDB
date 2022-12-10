<template>
  <q-input
    :borderless="!hasFocus && !changed"
    :outlined="hasFocus || changed"
    :modelValue="code"
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
  code: String,
  changed: Boolean,
});

const emit = defineEmits<{
  (e: 'update:code', value: string): void
  (e: 'update:changed', value: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const api = useApi();

const hasFocus = ref(false);
const loading = ref(false);
const uniqueName = ref<boolean|null>(null);

function change(val: string) {
  void checkCode(val);
  emit('update:code', val)
  emit('update:changed', true)
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
  padding-top: 20px;
}
</style>

<template>
  <q-input
    :borderless="!hasFocus && !changed"
    :outlined="hasFocus || changed || !!errorMessage"
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
    :error="!!errorMessage"
    :error-message="errorMessage"
  >
  </q-input>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, ref, watch} from 'vue';
import useApi from 'src/composables/api';
import {useQueryStore} from 'stores/query';

const props = defineProps({
  changed: Boolean,
});

const emit = defineEmits<{
  (e: 'update:changed', value: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const api = useApi();
const store = useQueryStore();

const hasFocus = ref(false);
const loading = ref(false);
const uniqueName = ref<boolean|null>(null);

let savedName: string = store.queryCode;

const code = computed<string>({
  get: (): string => store.queryCode,
  set: (code: string) => store.queryCode = code,
});

const errorMessage = computed<string>(() => {
  if (false === uniqueName.value && code.value.length > 0) {
    return t('queries.titleNotUnique');
  }

  if (store.attemptedToSaveQuery && code.value.length < 1) {
    return t('general.form.required');
  }

  return '';
});

function change(val: string) {
  void checkCode(val);
  code.value = val;
  emit('update:changed', true)
}

async function checkCode(code: string) {
  if (code === savedName) {
    uniqueName.value = true;
    return;
  }

  loading.value = true;

  try{
    await api.get(`queries/viewByCode/${code}`, () => null, {}, false)
    uniqueName.value = false;
  } catch {
    uniqueName.value = true;
  }

  loading.value = false;
}

watch(props, () => {
  if (false === props.changed) {
    savedName = code.value
  }
});

</script>

<style scoped>
/*noinspection CssUnusedSymbol*/
.q-input {
  max-width: 280px;
  font-size: 1.5rem;
  padding-top: 20px;
}
</style>

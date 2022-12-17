<template>
  <q-input
    ref="inputElement"
    :borderless="!hasFocus && !changed"
    :debounce="250"
    :error="!!errorMessage"
    :error-message="errorMessage"
    :input-class="{
      'query-name__input-field--hoverable': !hasFocus && !changed,
      'query-name__input-field': true
    }"
    :loading="loading"
    :modelValue="code"
    :outlined="hasFocus || changed || !!errorMessage"
    :placeholder="t('queries.unsaved')"
    :style="`width: ${textWidth + 32}px`"
    :title="t('general.edit')"
    autocomplete="off"
    class="query-name__input"
    dense
    maxlength="120"
    type="text"
    @blur="hasFocus = false"
    @focus="hasFocus = true"
    @update:model-value="change"
  >
  </q-input>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, ref, watch} from 'vue';
import useApi from 'src/composables/api';
import {useQueryStore} from 'stores/query';
import {QInput} from 'quasar';
import useTextWidth from 'src/composables/textWidth';

const props = defineProps({
  changed: Boolean,
});

const emit = defineEmits<{
  (e: 'update:changed', value: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const api = useApi();
const store = useQueryStore();
const textWidthUtil = useTextWidth();

const hasFocus = ref(false);
const loading = ref(false);
const uniqueName = ref<boolean | null>(null);
const inputElement = ref<QInput>();

let savedName: string = store.queryCode;
const defaultInputWidth = 248;

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

const textWidth = computed<number>(() => {
  const element = inputElement.value?.getNativeElement() as HTMLInputElement | undefined;

  if ( ! (element instanceof HTMLInputElement)) {
    return defaultInputWidth;
  }

  return textWidthUtil.getTextWidth(
    code.value,
    textWidthUtil.getFontProps(element)
  ) || defaultInputWidth;
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

  try {
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

<style>
.query-name__input {
  min-width: 200px;
  max-width: 100%;
  font-size: 1.5rem;
  padding-top: 20px;
}


/*noinspection CssUnusedSymbol*/
.query-name__input-field {
  transition: all var(--q-transition-duration) ease;
  text-overflow: ellipsis;
}

/*noinspection CssUnusedSymbol*/
.query-name__input-field--hoverable:hover {
  color: var(--q-primary);
  cursor: pointer;
}
</style>

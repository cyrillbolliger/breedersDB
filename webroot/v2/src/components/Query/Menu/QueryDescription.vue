<template>
  <q-input
    :borderless="!hasFocus && !changed"
    :outlined="hasFocus || changed"
    :modelValue="description"
    :placeholder="t('queries.description')"
    :label="t('queries.description')"
    autogrow
    autocomplete="off"
    @update:model-value="change"
    @focus="hasFocus = true"
    @blur="hasFocus = false"
    :loading="loading"
    dense
    class="q-mb-md query-description__input"
    :input-class="{
      'query-description__input-textarea--hoverable': !hasFocus && !changed,
      'query-description__input-textarea': true
    }"
    :title="t('general.edit')"
  >
  </q-input>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, ref, watch} from 'vue';
import {useQueryStore} from 'stores/query';

const props = defineProps({
  changed: Boolean,
});

const emit = defineEmits<{
  (e: 'update:changed', value: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const hasFocus = ref(false);
const loading = ref(false);

let savedDescription: string = store.queryDescription;

const description = computed<string>({
  get: (): string => store.queryDescription,
  set: (description: string) => store.queryDescription = description,
});

function change(val: string) {
  description.value = val;
  emit('update:changed', true)
}

watch(props, () => {
  if (false === props.changed) {
    savedDescription = description.value
  }
});

</script>

<style>
/*noinspection CssUnusedSymbol*/
.query-description__input-textarea {
  transition: all var(--q-transition-duration) ease;
}

/*noinspection CssUnusedSymbol*/
.query-description__input-textarea--hoverable:hover {
  color: var(--q-primary);
  cursor: pointer;
}
</style>

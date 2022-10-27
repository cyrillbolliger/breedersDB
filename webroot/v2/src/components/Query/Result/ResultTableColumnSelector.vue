<template>
  <q-select
    :disable="availableColumnsOption.length === 0"
    :label="t('queries.addColumn')"
    :model-value="null"
    :options="filteredOptions"
    use-input
    @filter="filterFn"
    @update:model-value="option => showColumn(option.value)"
  />
</template>
<script lang="ts" setup>

import {computed, onMounted, PropType, ref, watch} from 'vue';
import {QSelectProps, QTableColumn} from 'quasar';
import {useI18n} from 'vue-i18n';
import {useQueryStore} from 'stores/query';
import useQueryLocalStorageHelper from 'src/composables/queries/queryLocalStorageHelper';

const emit = defineEmits<{
  (e: 'update:modelValue', data: string[]): void
}>()

const props = defineProps({
  columns: {
    type: Object as PropType<QTableColumn[]>,
    required: true,
    default: () => [] as QTableColumn[],
  },
  modelValue: {
    type: Array as PropType<string[]>,
    required: true,
    default: () => [] as string[],
  }
});

const DEFAULT_DISPLAY_COLS_COUNT = 5;

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();
const localStorageHelper = useQueryLocalStorageHelper();

const lastBaseTable = ref<string>();

const baseTable = computed(() => {
  return store.baseTable;
});

const columns = computed<QTableColumn[]>(() => {
  return props.columns || [];
})

const allColumnNames = computed<string[]>(() => {
  return columns.value
    .map(item => item.name);
})

const visibleColumns = computed<string[]>({
  get: (): string[] => props.modelValue || [],
  set: (values: string[]) => emit('update:modelValue', values),
});

const availableColumnsOption = computed<{ label: string, value: string }[]>(() => {
  return columns.value
    .filter(column => -1 === visibleColumns.value.indexOf(column.name))
    .map(column => {
      return {label: column.label, value: column.name};
    });
});

const filteredOptions = ref(availableColumnsOption.value);

function showColumn(name: string) {
  if (-1 === visibleColumns.value.indexOf(name)) {
    visibleColumns.value = [...visibleColumns.value, name]
  }
}

function filterFn(value: string, update: Parameters<QSelectProps['onFilter']>[1]) {
  update(
    () => {
      if (value === '') {
        filteredOptions.value = availableColumnsOption.value
      }
      else {
        const needle = value.toLocaleLowerCase();
        filteredOptions.value = availableColumnsOption.value
          .filter(option => option.label.toLocaleLowerCase().indexOf(needle) > -1)
      }
    },

    // "ref" is the Vue reference to the QSelect
    ref => {
      if (value !== '' && ref.options && ref.options.length > 0) {
        ref.setOptionIndex(-1)
        ref.moveOptionSelection(1, true)
      }
    }
  )
}

function resetVisibleColumns() {
  let defaultCols: string[];

  if (allColumnNames.value.length <= DEFAULT_DISPLAY_COLS_COUNT) {
    defaultCols = allColumnNames.value;
  } else {
    defaultCols = allColumnNames.value.slice(0, DEFAULT_DISPLAY_COLS_COUNT);
  }

  visibleColumns.value = localStorageHelper.getVisibleColumns(defaultCols);
}

function resetVisibleColumnsOnBaseTableChange() {
  if (baseTable.value === lastBaseTable.value) {
    return;
  }

  if (allColumnNames.value.length === 0) {
    return;
  }

  resetVisibleColumns();
  lastBaseTable.value = baseTable.value;
}

watch(allColumnNames, resetVisibleColumnsOnBaseTableChange);

</script>

<style>
</style>

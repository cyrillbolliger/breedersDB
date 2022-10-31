<template>
  <q-td
    :props="cellProps"
  >
    <template v-if="isNonAggregatedMark">
      <!--suppress JSValidateTypes -->
      <ResultTableCellMark
        v-for="(mark, idx) in cellValue" :key="idx"
        :mark="mark"
      />
    </template>
    <template v-else-if="isAggregatedMarkValue">
      <ResultTableCellMark
        :mark="cellValue"
      />
    </template>

    <template v-else>
      {{ cellValue }}
    </template>
  </q-td>
</template>

<script lang="ts" setup>

import {computed, PropType} from 'vue';
import {QTableSlots} from 'quasar';
import {MarkCell} from 'src/models/query/query';
import ResultTableCellMark from 'components/Query/Result/ResultTableCellMark.vue';

const props = defineProps({
  cellProps: {
    type: Object as PropType<QTableSlots['body-cell']['scope']>,
  }
});

const cellValue = computed<string | MarkCell | MarkCell[]>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const value = props.cellProps.value as string | null | undefined | MarkCell | MarkCell[]; // eslint-disable-line @typescript-eslint/no-unsafe-member-access
  return value ? value : '';
});

const isAggregatedMarkValue = computed(() => {
  return 'object' === typeof cellValue.value
    && 'aggregated' in cellValue.value;
})

const isNonAggregatedMark = computed(() => {
  return Array.isArray(cellValue.value);
});

</script>

<style scoped>
</style>

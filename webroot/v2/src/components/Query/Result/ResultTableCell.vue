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
    <template v-else-if="isAggregatedMark">
      <!-- todo: nice popup with single values -->
      {{ cellValue.value }}
    </template>

    <template v-else>
      {{ cellValue }}
    </template>
  </q-td>
</template>

<script lang="ts" setup>

import {computed, PropType} from 'vue';
import {QTableSlots} from 'quasar';
import {AggregatedMarkCell, MarkCell} from 'src/models/query/query';
import ResultTableCellMark from 'components/Query/Result/ResultTableCellMark.vue';

const props = defineProps({
  cellProps: {
    type: Object as PropType<QTableSlots['body-cell']['scope']>,
  }
});

const cellValue = computed<string | number | boolean | AggregatedMarkCell | MarkCell[]>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const value = props.cellProps.value as null | undefined | string | number | boolean | AggregatedMarkCell | MarkCell[]; // eslint-disable-line @typescript-eslint/no-unsafe-member-access
  return null === value || undefined === value ? '' : value;
});

const isAggregatedMark = computed(() => {
  return 'object' === typeof cellValue.value
    && 'rawValues' in cellValue.value;
})

const isNonAggregatedMark = computed(() => {
  return Array.isArray(cellValue.value);
});

</script>

<style scoped>
</style>

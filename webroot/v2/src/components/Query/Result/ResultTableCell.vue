<template>
  <q-td :props="cellProps">
    <template v-if="isMarkValue">
      <ResultTableCellMark
        v-for="(mark, idx) in cellValue" :key="idx"
        :mark="mark"
      />
    </template>

    <template v-else>
      {{ cellValue }}
    </template>
  </q-td>
</template>

<script setup lang="ts">

import {computed, PropType} from 'vue';
import {QTableSlots} from 'quasar';
import {MarkCell} from 'src/models/query/query';
import ResultTableCellMark from 'components/Query/Result/ResultTableCellMark.vue';

const props = defineProps({
  cellProps: {
    type: Object as PropType<QTableSlots['body-cell']['scope']>,
  }
});

const cellValue = computed<string|MarkCell[]>(() => {
  // noinspection TypeScriptUnresolvedVariable
  const value = props.cellProps.value as string|null|undefined|MarkCell[]; // eslint-disable-line @typescript-eslint/no-unsafe-member-access
  return value ? value : '';
});

const isMarkValue = computed(() => {
  return Array.isArray(cellValue.value);
})

</script>

<style>

</style>

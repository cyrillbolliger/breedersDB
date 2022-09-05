<template>
  <FilterTree
    :node="filter.getBaseFilter()"
    :options="options"
    :operand="filter.getBaseFilter().operand"
  />
</template>

<script setup lang="ts">
import FilterTree from 'components/Query/FilterTree.vue';
import useFilter from 'src/composables/queries/filter';
import {DataType} from 'src/store/module-query/state';

const filter = useFilter();

// todo: replace stubs
const options = [
  {label: 'Trees -> ID', value: 'trees_id', type: DataType.Integer},
  {label: 'Trees -> Row', value: 'trees_row', type: DataType.Float},
  {label: 'Marks -> Note', value: 'marks_note', type: DataType.String},
  {label: 'Marks -> Photo', value: 'marks_photo', type: DataType.Photo},
  {label: 'Marks -> Date', value: 'marks_date', type: DataType.Date},
  {label: 'Marks -> Original', value: 'marks_original', type: DataType.Boolean},
];

</script>

<style>

</style>

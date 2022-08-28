<template>
  <FilterRule
    :options="options1"
  />
</template>

<script setup lang="ts">
import FilterRule from 'components/Query/FilterRule.vue';
import {DataType} from 'src/models/filterOptions';





// todo: replace stubs
const options1 = [
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

<template>
  <q-chip
    dense
    size="sm"
    :color="bgColor"
  >
    {{ mark.value }}
  </q-chip>
</template>

<script lang="ts" setup>
import {computed, PropType} from 'vue';
import {MarkCell} from 'src/models/query/query';

type MarkEntityType = 'tree' | 'batch' | 'variety';

const props = defineProps({
  mark: {
    type: Object as PropType<MarkCell>,
  }
})

const type = computed<MarkEntityType>(() => {
  // noinspection TypeScriptUnresolvedVariable
  if (props.mark.tree_id) {
    return 'tree';
  }
  // noinspection TypeScriptUnresolvedVariable
  if (props.mark.variety_id) {
    return 'variety';
  }
  return 'batch';
})

const bgColor = computed(() => {
  switch (type.value) {
    case 'tree':
      return 'green-2';
    case 'variety':
      return 'amber-2';
    default:
      return 'grey-2';
  }
})

</script>

<style>

</style>

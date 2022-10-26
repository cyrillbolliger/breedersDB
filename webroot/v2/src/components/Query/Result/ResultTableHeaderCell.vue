<template>
  <q-th :props="cellProps">
    <q-icon
      class="drag-handle"
      name="drag_indicator"
      size="xs"
      @mousedown="$emit('dragMouseDown')"
      @mouseup="$emit('dragMouseUp')"
    />
    {{ cellProps.col.label }}
    <q-btn
      dense
      flat
      icon="close"
      round
      size="xs"
      @click.stop="$emit('hideColumn', cellProps.col.name)"
    />
  </q-th>
</template>

<script setup lang="ts">
import {PropType} from 'vue';
import {QTableSlots} from 'quasar';

defineEmits<{
  (e: 'hideColumn'): void
  (e: 'dragMouseDown'): void
  (e: 'dragMouseUp'): void
}>()

defineProps({
  cellProps: {
    type: Object as PropType<QTableSlots['header-cell']['scope']>,
  }
});

</script>

<style scoped>
th {
  top: 0;
  white-space: nowrap;
}

.drag-handle {
  color: rgba(0, 0, 0, 0.4);
  cursor: grab;
}

.drag-handle:hover {
  color: var(--q-primary);
}
</style>

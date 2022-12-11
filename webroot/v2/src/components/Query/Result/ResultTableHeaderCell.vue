<template>
  <q-th
    :props="cellProps"
    :draggable="dragging"
    @dragend="dragging = false"
    @dragstart="dragStart"
  >
    <div
      class="drop-zone drop-before"
      :class="{'drop-zone-active': dragOverBefore && !dragging}"
      @dragenter="dragOverBefore = true"
      @dragleave="dragOverBefore = false"
      @drop.prevent="e => drop('before', e.dataTransfer.getData('text/plain'))"
      @dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
    />
    <q-icon
      class="drag-handle"
      name="drag_indicator"
      size="xs"
      @mousedown="dragging = true"
      @mouseup="dragging = false"
      @click.prevent.stop=""
    />
    {{ cellProps.col.label }}
    <div
      class="drop-zone drop-after"
      :class="{'drop-zone-active': dragOverAfter && !dragging}"
      @dragenter="dragOverAfter = true"
      @dragleave="dragOverAfter = false"
      @drop.prevent="e => drop('after', e.dataTransfer.getData('text/plain'))"
      @dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
    />
    <q-btn
      dense
      flat
      icon="close"
      round
      size="xs"
      @click.stop="$emit('hideColumn', colName)"
    />
  </q-th>
</template>

<script setup lang="ts">
import {computed, PropType, ref} from 'vue';
import {QTableSlots} from 'quasar';

const emit = defineEmits<{
  (e: 'hideColumn', colName: string): void
  (e: 'colDropped', thisColName: string, droppedColName: string, pos: 'before' | 'after'): void
}>()

const props = defineProps({
  cellProps: {
    type: Object as PropType<QTableSlots['header-cell']['scope']>,
  }
});

const dragging = ref(false);
const dragOverBefore = ref(false);
const dragOverAfter = ref(false);

const colName = computed<string>(() => {
  // noinspection TypeScriptUnresolvedVariable
  return props.cellProps.col.name; // eslint-disable-line
})

function dragStart(e: DragEvent) {
  e.dataTransfer.dropEffect = 'move';
  e.dataTransfer.setData('text/plain', colName.value);
}

function drop(pos: 'before'|'after', droppedColName: string) {
  dragOverBefore.value = false;
  dragOverAfter.value = false;
  emit('colDropped', colName.value, droppedColName, pos);
}

</script>

<style scoped>
th {
  white-space: nowrap;
}

.drag-handle {
  color: rgba(0, 0, 0, 0.4);
  cursor: grab;
}

.drag-handle:hover {
  color: var(--q-primary);
}

.drop-zone {
  height: 48px;
  width: 50%;
  border: 0 solid transparent;
  display: inline-block;
  position: absolute;
  top: 0;
}

.drop-before {
  left: 0;
  border-left-width: 4px;
}

.drop-after {
  right: 0;
  border-right-width: 4px;
}

.drop-zone-active {
  border-color: var(--q-primary);
}
</style>

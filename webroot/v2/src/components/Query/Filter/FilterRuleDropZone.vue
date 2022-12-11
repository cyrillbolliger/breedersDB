<template>
  <div
    class="filter-rule__drop"
    @dragleave="mouseInDropZone = false"
    @dragenter="mouseInDropZone = true"
    @dragover.prevent="$event.dataTransfer.dropEffect = 'move'"
    :class="{
        'filter-rule__drop--hover': markHover,
        'filter-rule__drop--active': active,
        'filter-rule__drop--primary': color === 'primary',
        'filter-rule__drop--accent': color === 'accent',
      }"
  />
</template>

<script setup lang="ts">
import {computed, PropType, ref, watch} from 'vue';

const props = defineProps({
  active: {
    type: Boolean,
    required: true,
  },
  color: {
    type: String as PropType<'primary'|'accent'>,
    default: 'primary'
  },
  dragging: {
    type: Boolean,
    required: true,
  }
})

const mouseInDropZone = ref(false);

const markHover = computed(() => {
  return mouseInDropZone.value && props.active
})

watch(() => props.dragging, dragging => {
  if (false === dragging) {
    mouseInDropZone.value = false;
  }
})
</script>

<style scoped>
.filter-rule__drop {
  height: 18px;
  width: 100%;
  opacity: 0.25;
  display: none;
  position: absolute;
  left: 0;
  z-index: -1;
}

.filter-rule__drop--active {
  display: block;
  z-index: 10;
}

.filter-rule__drop--primary {
  background-color: var(--q-primary);
}

.filter-rule__drop--accent {
  background-color: var(--q-accent);
}

.filter-rule__drop--hover {
  opacity: 1;
}
</style>

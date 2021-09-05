<template>
  <q-item>
    <q-item-section>

      <q-item-label
        v-if="title"
        class="text-subtitle1 q-pb-sm"
      >{{title}}</q-item-label>

      <slot></slot>

      <q-item-label
        v-if="note"
        class="text-caption q-mt-sm"
      >{{note}}</q-item-label>

    </q-item-section>
  </q-item>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({
  name: 'MarkInputItem',
  props: {
    title: {
      type: String,
    },
    note: {
      type: String,
    }
  },
})
</script>

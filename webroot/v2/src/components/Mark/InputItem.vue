<template>
  <q-item>
    <q-item-section>

      <div class="row justify-between items-start">
        <q-item-label
          v-if="title"
          class="text-subtitle1 text-weight-bold q-pb-sm"
        >{{title}}</q-item-label>
        <q-badge
          v-if="value || value === 0"
          color="black"
          :label="value"
          class="text-weight-bold mark-input-item__badge"
        />
      </div>

      <slot></slot>

      <q-item-label
        v-if="note"
        class="q-mt-sm mark-input-item__label"
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
    },
    value: {
      default: undefined,
    }
  },
})
</script>

<style scoped>
  .mark-input-item__label{
    hyphens: auto;
    overflow-wrap: break-word;
    font-weight: bolder;
    font-size: 0.8125rem;
  }
  .mark-input-item__badge {
    font-size: 0.9125rem;
    line-height: 1em;
  }
</style>

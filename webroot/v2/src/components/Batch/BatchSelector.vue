<template>
  <variety-or-batch-selector
    :label="t('marks.selectBatch.searchSelectLabel')"
    endpoint="batches"
    sortBy="crossing_batch"
    :limit="20"
    :resultLabelExtractor="v => (v as Batch).crossing_batch"
    @selected="$emit('selected', $event)"
  />
</template>

<script lang="ts" setup>
import { type Batch } from 'src/models/batch';
import VarietyOrBatchSelector from '../Util/VarietyOrBatchSelector.vue';
import { useI18n } from 'vue-i18n';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

defineEmits<{ (e: 'selected', value: Batch | undefined): void }>()
</script>
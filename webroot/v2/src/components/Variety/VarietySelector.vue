<template>
  <variety-or-batch-selector
    :label="t('marks.selectVariety.searchSelectLabel')"
    endpoint="varieties"
    sortBy="convar"
    :limit="20"
    :resultLabelExtractor="r => (r as Variety).convar"
    @selected="$emit('selected', $event)"
  />
</template>

<script lang="ts" setup>
import type { Variety } from 'src/models/variety';
import VarietyOrBatchSelector from '../Util/VarietyOrBatchSelector.vue';
import { useI18n } from 'vue-i18n';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

defineEmits<{ (e: 'selected', value: Variety | undefined): void }>()
</script>

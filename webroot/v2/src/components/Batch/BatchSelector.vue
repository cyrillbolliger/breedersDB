<template>
  <q-select
    v-model="selected"
    :loading="working"
    :options="options"
    autofocus
    clearable
    fill-input
    hide-selected
    :hint="t('general.typeToSearch')"
    input-debounce="100"
    :label="t('marks.selectBatch.searchSelectLabel')"
    outlined
    use-input
    @filter="filterFn"
    @filter-abort="abortFilterFn"
  >
    <template v-slot:no-option>
      <q-item>
        <q-item-section class="text-grey">
          {{t('general.noResults')}}
        </q-item-section>
      </q-item>
    </template>
    <template v-slot:after-options v-if="optionCount > limit">
      <q-item>
        <q-item-section class="text-grey">
          {{ t('general.moreResults', {limit, count: optionCount})}}
        </q-item-section>
      </q-item>
    </template>
  </q-select>

  <q-btn
    :disabled="!selected"
    :label="t('general.next')"
    color="primary"
    @click="$emit('selected', selected?.entity)"
  />
</template>

<script lang="ts" setup>
import {ref} from 'vue';
import useApi from 'src/composables/api';
import {QSelectProps} from 'quasar';
import {useI18n} from 'vue-i18n';
import { Batch } from 'src/models/batch';

defineEmits<{ (e: 'selected', value: Batch | undefined): void }>()

const {get, working} = useApi()
const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const limit = 20

const selected = ref<{ label: string, value: number, entity: Batch } | null>(null)
const options = ref<{ label: string, value: number, entity: Batch }[]>([])
const optionCount = ref(0)

let requestController: AbortController | null

function filterFn(val: string, update: Parameters<NonNullable<QSelectProps['onFilter']>>[1]) {
  requestController?.abort()
  requestController = new AbortController()

  void get<{
    count: number,
    offset: number,
    sortBy: string | null,
    order: 'asc' | 'desc' | null,
    limit: number | null,
    results: Batch[]
  }>(
    `/batches?limit=${limit}&sortBy=crossing_batch&order=asc&term=${val}`,
    () => null,
    {signal: requestController.signal}
  ).then(data => {
    optionCount.value = data?.count || 0
    const varieties = data?.results || []
    update(() => {
      options.value = varieties.map(v => ({label: v.crossing_batch, value: v.id, entity: v}))
    })
  })
}

function abortFilterFn() {
  requestController?.abort()
}
</script>

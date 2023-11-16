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
    :label="label"
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
import {Variety} from 'src/models/variety';
import {Batch} from 'src/models/batch';
import {PropType, ref} from 'vue';
import useApi from 'src/composables/api';
import {QSelectProps} from 'quasar';
import {useI18n} from 'vue-i18n';

defineEmits<{ (e: 'selected', value: Batch | Variety | undefined): void }>()
const props = defineProps({
  label: { type: String, required: true },
  endpoint: { type: String, required: true },
  sortBy: { type: String, required: true },
  order: { type: String, default: 'asc' },
  limit: { type: Number, default: 20 },
  resultLabelExtractor: { type: Function as PropType<(result: Batch | Variety) => string>, required: true }
})
const {get, working} = useApi()
const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method

const selected = ref<{ label: string, value: number, entity: Batch | Variety } | null>(null)
const options = ref<{ label: string, value: number, entity: Batch | Variety }[]>([])
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
    results: Batch[] | Variety[]
  }>(
    `/${props.endpoint}?limit=${props.limit}&sortBy=${props.sortBy}&order=${props.order}&term=${val}`,
    () => null,
    {signal: requestController.signal}
  ).then(data => {
    optionCount.value = data?.count || 0
    const results = data?.results || []
    update(() => {
      options.value = results.map(r => ({label: props.resultLabelExtractor(r), value: r.id, entity: r}))
    })
  })
}

function abortFilterFn() {
  requestController?.abort()
}
</script>

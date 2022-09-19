<template>
  <q-page padding>
    <h5 class="q-mb-sm q-mt-sm">{{ t('queries.query') }}</h5>

    <p class="text-overline q-mb-none">{{ t('queries.baseTable') }}</p>
    <q-btn-toggle
      :options="baseOptions"
      v-model="baseTable"
      no-wrap
    />

    <p class="text-overline q-mb-none q-mt-lg" v-if="baseTable === BaseTable.Batches">{{ t('queries.batchFilter') }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else-if="baseTable === BaseTable.Varieties">{{
        t('queries.varietyFilter')
      }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else-if="baseTable === BaseTable.Trees">{{ t('queries.treeFilter') }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else>{{ t('queries.defaultFilter') }}</p>
    <FilterTreeRoot
      :filter="baseFilter"
    />

    <template v-if="marksAvailable">
      <p class="text-overline q-mb-none q-mt-lg">{{ t('queries.markFilter') }}</p>
      <FilterTreeRoot
        :filter="markFilter"
      />
    </template>

    <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>
  </q-page>
</template>

<script setup lang="ts">
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import {computed} from 'vue';
import FilterTreeRoot from 'components/Query/FilterTreeRoot.vue';
import {useQueryStore} from 'stores/query';
import {BaseTable} from 'src/models/query/query';
import useFilter from 'src/composables/queries/filter';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()
const store = useQueryStore()
const filter = useFilter()

layout.setToolbarTitle(t('queries.title'))
layout.setToolbarTabs([])
layout.setToolbarBreadcrumbs([])

const baseOptions = [
  {value: BaseTable.Crossings, label: t('queries.crossings')},
  {value: BaseTable.Batches, label: t('queries.batches')},
  {value: BaseTable.Varieties, label: t('queries.varieties')},
  {value: BaseTable.Trees, label: t('queries.trees')},
  {value: BaseTable.MotherTrees, label: t('queries.motherTrees')},
  {value: BaseTable.ScionsBundles, label: t('queries.scionsBundles')},
]

const baseTable = computed<BaseTable>({
  get: () => store.baseTable,
  set: (val: BaseTable) => store.baseTable = val,
});

const marksAvailable = computed<boolean>(() => {
  return baseTable.value === BaseTable.Batches
    || baseTable.value === BaseTable.Varieties
    || baseTable.value === BaseTable.Trees
});

const baseFilter = computed(() => filter.getBaseFilter());
const markFilter = computed(() => filter.getMarkFilter());

</script>

<style>

</style>

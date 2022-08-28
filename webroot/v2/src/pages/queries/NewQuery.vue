<template>
  <q-page padding>
    <h5 class="q-mb-sm q-mt-sm">{{ t('queries.query') }}</h5>

    <p class="text-overline q-mb-none">{{ t('queries.baseTable') }}</p>
    <q-btn-toggle
      :options="baseOptions"
      v-model="base"
      no-wrap
    />

    <p class="text-overline q-mb-none q-mt-lg" v-if="base === BaseTable.Batches">{{ t('queries.batchFilter') }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else-if="base === BaseTable.Varieties">{{
        t('queries.varietyFilter')
      }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else-if="base === BaseTable.Trees">{{ t('queries.treeFilter') }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else>{{ t('queries.defaultFilter') }}</p>
    <FilterSet />

    <template v-if="marksAvailable">
      <p class="text-overline q-mb-none q-mt-lg">{{ t('queries.markFilter') }}</p>
      Filter 2
    </template>

    <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>
  </q-page>
</template>

<script setup lang="ts">
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import {BaseTable} from 'src/store/module-query/state';
import {computed} from 'vue';
import {useStore} from 'src/store';
import FilterSet from 'components/Query/FilterSet.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()
const store = useStore()

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

const base = computed<BaseTable>({
  get: () => store.getters['query/base'], // eslint-disable-line
  set: (val: BaseTable) => store.dispatch('query/base', val)
});

const marksAvailable = computed<boolean>(() => {
  return base.value === BaseTable.Batches
    || base.value === BaseTable.Varieties
    || base.value === BaseTable.Trees
});

</script>

<style>

</style>

<template>
  <q-page padding>
    <h5 class="q-mb-sm q-mt-sm">{{ t('queries.query') }}</h5>

    <p class="text-overline q-mb-none">{{ t('queries.baseTable') }}</p>
    <q-btn-toggle
      :options="baseTableOptions"
      v-model="baseTable"
      no-wrap
    />

    <p class="text-overline q-mb-none q-mt-lg" v-if="baseTable === BaseTable.Batches">{{ t('queries.batchFilter') }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else-if="baseTable === BaseTable.Varieties">{{
        t('queries.varietyFilter')
      }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else-if="baseTable === BaseTable.Trees">{{ t('queries.treeFilter') }}</p>
    <p class="text-overline q-mb-none q-mt-lg" v-else>{{ t('queries.defaultFilter') }}</p>
    <!--suppress JSValidateTypes -->
    <FilterTreeRoot
      :filter="baseFilter"
      :options="baseFilterOptions"
    />

    <template v-if="marksAvailable">
      <p class="text-overline q-mb-none q-mt-lg">{{ t('queries.markFilter') }}</p>
      <!--suppress JSValidateTypes -->
      <FilterTreeRoot
        :filter="markFilter"
        :options="markFilterOptions"
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
import {FilterDataType} from 'src/models/query/filterTypes';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()
const store = useQueryStore()

layout.setToolbarTitle(t('queries.title'))
layout.setToolbarTabs([])
layout.setToolbarBreadcrumbs([])

const baseTableOptions = [
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

const baseFilter = computed(() => store.baseFilter);
const markFilter = computed(() => store.markFilter);

// todo: replace stubs
const options = [
  {label: 'Trees -> ID', value: 'trees_id', type: FilterDataType.Integer},
  {label: 'Trees -> Row', value: 'trees_row', type: FilterDataType.Float},
  {label: 'Marks -> Note', value: 'marks_note', type: FilterDataType.String},
  {label: 'Marks -> Photo', value: 'marks_photo', type: FilterDataType.Photo},
  {label: 'Marks -> Date', value: 'marks_date', type: FilterDataType.Date},
  {label: 'Marks -> Original', value: 'marks_original', type: FilterDataType.Boolean},
];

const markFilterOptions = options;
const baseFilterOptions = options;

</script>

<style>

</style>

<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('queries.query') }}</h5>

  <p class="text-overline q-mb-none">{{ t('queries.baseTable') }}</p>
  <q-btn-toggle
    v-model="baseTable"
    :disable="loading"
    :options="baseTableOptions"
    no-wrap
  />

  <p v-if="baseTable === BaseTable.Batches" class="text-overline q-mb-none q-mt-lg">{{ t('queries.batchFilter') }}</p>
  <p v-else-if="baseTable === BaseTable.Varieties" class="text-overline q-mb-none q-mt-lg">{{
      t('queries.varietyFilter')
    }}</p>
  <p v-else-if="baseTable === BaseTable.Trees" class="text-overline q-mb-none q-mt-lg">{{
      t('queries.treeFilter')
    }}</p>
  <p v-else class="text-overline q-mb-none q-mt-lg">{{ t('queries.defaultFilter') }}</p>
  <!--suppress JSValidateTypes -->
  <FilterTreeRoot
    v-if="!loading"
    :filter="baseFilter"
    :options="baseFilterOptions"
  />
  <q-spinner
    v-else
    color="primary"
    size="4em"
  />

  <template v-if="marksAvailable">
    <p class="text-overline q-mb-none q-mt-lg">{{ t('queries.markFilter') }}</p>
    <!--suppress JSValidateTypes -->
    <FilterTreeRoot
      v-if="!loading"
      :filter="markFilter"
      :options="markFilterOptions"
    />
    <q-spinner
      v-else
      color="primary"
      size="4em"
    />
  </template>
</template>

<script lang="ts" setup>
import FilterTreeRoot from 'components/Query/Filter/FilterTreeRoot.vue';
import {useI18n} from 'vue-i18n';
import {useQueryStore} from 'stores/query';
import {BaseTable} from 'src/models/query/query';
import {computed, onMounted, ref, watch} from 'vue';
import {
  PropertySchema,
} from 'src/models/query/filterOptionSchema';
import useQueryLocalStorageHelper from 'src/composables/queries/queryLocalStorageHelper';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore()
const localStorageHelper = useQueryLocalStorageHelper();

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
  set: (val: BaseTable) => {
    store.baseTable = val;
    localStorageHelper.setBaseTable(val);
  }
});

const baseFilter = computed(() => store.getBaseFilter);
const markFilter = computed(() => store.getMarkFilter);
const marksAvailable = computed<boolean>(() => store.marksAvailable);

const loading = ref<boolean>(true);

const baseFilterOptions = computed<PropertySchema[]>(() => store.baseFilterOptions);
const markFilterOptions = computed<PropertySchema[]>(() => store.markFilterOptions);

async function loadFilterOptions() {
  loading.value = true;
  await store.ensureSchemasLoaded();
  setFilters();
  loading.value = false;
}

function setFilters() {
  store.setBaseFilter(localStorageHelper.getBaseFilter(baseFilter.value));
  store.setMarkFilter(localStorageHelper.getMarkFilter(markFilter.value));
}

watch(baseTable, setFilters);

onMounted(() => {
  void loadFilterOptions();
});

</script>

<style>

</style>

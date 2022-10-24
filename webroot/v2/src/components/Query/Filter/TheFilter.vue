<template>
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
  <p class="text-overline q-mb-none q-mt-lg" v-else-if="baseTable === BaseTable.Trees">{{
      t('queries.treeFilter')
    }}</p>
  <p class="text-overline q-mb-none q-mt-lg" v-else>{{ t('queries.defaultFilter') }}</p>
  <!--suppress JSValidateTypes -->
  <FilterTreeRoot
    :filter="baseFilter"
    :options="baseFilterOptions"
    v-if="!loading"
  />
  <q-spinner
    color="primary"
    size="4em"
    v-else
  />

  <template v-if="marksAvailable">
    <p class="text-overline q-mb-none q-mt-lg">{{ t('queries.markFilter') }}</p>
    <!--suppress JSValidateTypes -->
    <FilterTreeRoot
      :filter="markFilter"
      :options="allFilterOptions?.Marks || []"
      v-if="!loading"
    />
    <q-spinner
      color="primary"
      size="4em"
      v-else
    />
  </template>
</template>

<script setup lang="ts">
import FilterTreeRoot from 'components/Query/Filter/FilterTreeRoot.vue';
import {useI18n} from 'vue-i18n';
import {useQueryStore} from 'stores/query';
import useApi from 'src/composables/api';
import {BaseTable} from 'src/models/query/query';
import {computed, onMounted, ref} from 'vue';
import {
  FilterOptionSchemas,
  PropertySchema,
} from 'src/models/query/filterOptionSchema';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore()
const api = useApi()

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

const baseFilter = computed(() => store.baseFilter);
const markFilter = computed(() => store.markFilter);
const marksAvailable = computed<boolean>(() => store.marksAvailable);

const loading = ref<boolean>(true);
const allFilterOptions = ref<FilterOptionSchemas | null>(null);
const markFormPropertyFilterOptions = ref<PropertySchema[]>([]);

const baseFilterOptions = computed<PropertySchema[]>(() => {
  if (!allFilterOptions.value){
    return [];
  }

  const options: PropertySchema[] = allFilterOptions.value[baseTable.value] || [];

  if (marksAvailable.value) {
    options.push(...markFormPropertyFilterOptions.value);
  }

  return options;
});

async function loadFilterOptions() {
  loading.value = true;

  const base = api.get<FilterOptionSchemas>('queries/get-filter-schemas')
    .then(data => allFilterOptions.value = data as FilterOptionSchemas)
  const mark = store.maybeLoadMarkFormProperties()
    .then(() => markFormPropertyFilterOptions.value = store.markPropertySchema(t('queries.Marks') + ' > '))

  await Promise.all([base, mark])
    .then(() => loading.value = false);
}

onMounted(() => {
  void loadFilterOptions();
});

</script>

<style>

</style>

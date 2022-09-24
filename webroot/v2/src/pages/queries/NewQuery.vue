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
        :options="markFilterOptions"
        v-if="!loading"
      />
      <q-spinner
        color="primary"
        size="4em"
        v-else
      />
    </template>

    <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>
  </q-page>
</template>

<script setup lang="ts">
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import {computed, onMounted, ref} from 'vue';
import FilterTreeRoot from 'components/Query/FilterTreeRoot.vue';
import {useQueryStore} from 'stores/query';
import {BaseTable} from 'src/models/query/query';
import useApi from 'src/composables/api';
import {MarkFormFieldType, MarkFormProperty} from 'src/models/form';
import {
  BaseFilterOptionSchemas,
  PropertySchema,
  PropertySchemaOptions,
  PropertySchemaOptionType
} from 'src/models/query/filterOptionSchema';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()
const store = useQueryStore()
const api = useApi()

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

const loading = ref<boolean>(true);
const allBaseFilterOptions = ref<BaseFilterOptionSchemas | null>(null);
const markFilterOptions = ref<PropertySchema[]>([]);

const baseFilterOptions = computed<PropertySchema[]>(() => {
  if (!allBaseFilterOptions.value){
    return [];
  }

  const options: PropertySchema[] = allBaseFilterOptions.value[baseTable.value] || [];

  if (marksAvailable.value) {
    options.push(...markFilterOptions.value);
  }

  return options;
});

async function loadFilterOptions() {
  loading.value = true;

  const base = api.get<BaseFilterOptionSchemas>('queries/get-base-filter-schemas')
    .then(data => allBaseFilterOptions.value = data as BaseFilterOptionSchemas)
  const mark = api.get<MarkFormProperty[]>('mark-form-properties')
    .then(data => setMarkFilterOptions(data as MarkFormProperty[]))

  await Promise.all([base, mark])
    .then(() => loading.value = false);
}

function setMarkFilterOptions(markFormProperties: MarkFormProperty[]) {
  markFilterOptions.value = markFormProperties.map(item => {
    return {
      name: `Mark.${item.id}`,
      label: t('queries.Marks') + ' > ' + item.name,
      options: convertMarkFormPropertyToSchemaOption(item),
    } as PropertySchema
  });
}

function convertMarkFormPropertyToSchemaOption(property: MarkFormProperty): PropertySchemaOptions {
  const type = {
    [MarkFormFieldType.Integer]: 'integer',
    [MarkFormFieldType.Float]: 'double',
    [MarkFormFieldType.Boolean]: 'boolean',
    [MarkFormFieldType.Date]: 'date',
    [MarkFormFieldType.String]: 'string',
    [MarkFormFieldType.Photo]: 'photo',
  }[property.field_type] || undefined;

  if (undefined === type){
    throw Error(`Unknown mark form property field type: ${property.field_type}`);
  }

  // noinspection TypeScriptValidateTypes
  const options: PropertySchemaOptions = {
    type: type as PropertySchemaOptionType,
    allowEmpty: false
  }

  switch (type) {
    case 'string':
      // noinspection TypeScriptUnresolvedVariable
      options.validation = {
        maxLen: 255,
        pattern: null,
      }
      break;
    case 'integer':
    case 'double':
      // noinspection TypeScriptUnresolvedVariable
      options.validation = property.number_constraints
      break;
    default:
      break;
  }

  return options;
}

onMounted(() => {
  void loadFilterOptions();
});

</script>

<style>

</style>

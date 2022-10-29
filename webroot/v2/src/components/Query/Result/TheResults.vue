<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>

  <template v-if="isValid">
    <ResultsTable
      :loading="loading"
      :result="result"
      @requestData="requestData"
    />

    <ResultDebug
      v-if="result?.debug"
      :data="result.debug"
    />
  </template>

  <template v-else>
    <q-banner class="bg-grey-3">
      <template #avatar>
        <q-icon name="warning"/>
      </template>
      {{ t('queries.invalidNoResults') }}
    </q-banner>
  </template>

</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, onMounted, ref, watch} from 'vue';
import {useQueryStore} from 'stores/query';
import useApi from 'src/composables/api';
import {debounce, QTable} from 'quasar';
import {QueryResponse} from 'src/models/query/query';
import ResultsTable from 'components/Query/Result/ResultTable.vue';
import ResultDebug from 'components/Query/Result/ResultDebug.vue';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const queryStore = useQueryStore();
const api = useApi();

const loading = ref(false);
const result = ref<QueryResponse | null>(null);

const baseTable = computed(() => queryStore.baseTable);
const baseFilter = computed(() => queryStore.getBaseFilter);
const markFilter = computed(() => queryStore.getMarkFilter);
const marksAvailable = computed<boolean>(() => queryStore.marksAvailable);

const isValid = computed(() => {
  return marksAvailable.value
    ? baseFilter.value.isValid() && markFilter.value.isValid()
    : baseFilter.value.isValid()
})

function requestData(requestProps: Parameters<QTable['onRequest']>[0]) {
  const {page, rowsPerPage, sortBy, descending} = requestProps.pagination
  void loadResults({
    offset: (page - 1) * rowsPerPage,
    sortBy,
    order: descending ? 'desc' : 'asc',
    limit: rowsPerPage,
  });
}

function getUrl(pagination?: { offset: number, sortBy: string | null, order: 'asc' | 'desc', limit: number }) {
  let url = 'queries/find-results';
  url += '?offset=' + String(pagination?.offset || 0);

  if (pagination?.sortBy && pagination?.order) {
    url += '&sortBy=' + pagination.sortBy;
    url += '&order=' + pagination.order;
  }

  if (pagination?.limit) {
    url += '&limit=' + pagination.limit.toString();
  }

  return url;
}

async function loadResults(pagination?: { offset: number, sortBy: string | null, order: 'asc' | 'desc', limit: number }) {
  if ( ! isValid.value) {
    return;
  }

  loading.value = true

  const data = {
    baseTable: baseTable.value,
    baseFilter: baseFilter.value,
    markFilter: marksAvailable.value ? markFilter.value : null,
  }

  result.value = null;
  await api.post<typeof data, QueryResponse>(getUrl(pagination), data)
    .then((resp: QueryResponse) => result.value = resp)

  loading.value = false
}

const debouncedLoadResults = debounce(loadResults, 2000);

function queueLoadResults() {
  loading.value = true
  debouncedLoadResults.cancel()
  void debouncedLoadResults()
}

onMounted(queueLoadResults);
watch(baseFilter.value, queueLoadResults);
watch(markFilter.value, queueLoadResults);
watch(baseTable, queueLoadResults);
</script>

<style scoped>
code {
  display: block;
}
</style>

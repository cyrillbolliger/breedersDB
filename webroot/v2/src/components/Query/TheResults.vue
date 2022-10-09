<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>

  <template v-if="isValid">
    <template v-if="error">
      error!
    </template>
    <template v-else>
<!--      <p>Count: {{ result?.count || 'loading...' }}</p>-->

      <ResultsTable
        :loading="loading"
        :result="result"
        @requestData="requestData"
      />

      <!--      <code>-->
      <!--        SQL: {{ result?.debug?.sql }}-->
      <!--      </code>-->

    </template>

    <!--    <pre>-->
    <!--      {{ baseFilter }}-->
    <!--      <template v-if="marksAvailable">-->
    <!--        {{ markFilter }}-->
    <!--      </template>-->
    <!--    </pre>-->
  </template>
  <template v-else>
    Invalid
  </template>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, onMounted, ref, watch} from 'vue';
import {useQueryStore} from 'stores/query';
import useApi from 'src/composables/api';
import {debounce, QTable} from 'quasar';
import {QueryResponse} from 'src/models/query/query';
import ResultsTable from 'src/components/Query/ResultTable.vue';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const queryStore = useQueryStore();
const api = useApi();

const loading = ref(false);
const result = ref<QueryResponse | null>(null);
const error = ref(false);

const baseTable = computed(() => queryStore.baseTable);
const baseFilter = computed(() => queryStore.baseFilter);
const markFilter = computed(() => queryStore.markFilter);
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
  });
}

function getUrl(pagination?: { offset: number, sortBy: string | null, order: 'asc' | 'desc' }) {
  let url = 'queries/find-results';
  url += '?offset=' + String(pagination?.offset || 0);

  if (pagination?.sortBy && pagination?.order) {
    url += '&sortBy=' + pagination.sortBy;
    url += '&order=' + pagination.order;
  }

  return url;
}

async function loadResults(pagination?: { offset: number, sortBy: string | null, order: 'asc' | 'desc' }) {
  if ( ! isValid.value) {
    return;
  }

  loading.value = true
  error.value = false

  const data = {
    baseTable: baseTable.value,
    baseFilter: baseFilter.value,
    markFilter: marksAvailable.value ? markFilter.value : null,
  }

  try {
    result.value = null;
    await api.post<typeof data, QueryResponse>(getUrl(pagination), data)
      .then((resp: QueryResponse) => result.value = resp)
  } catch (e) {
    console.error(e);
    error.value = true;
  }

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

<style>

</style>

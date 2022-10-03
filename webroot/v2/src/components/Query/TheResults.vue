<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>

  <template v-if="isValid">
    <template v-if="loading">
      loading...
    </template>
    <template v-else-if="error">
      error!
    </template>
    <template v-else>
      {{ result }}
    </template>

    <pre>
      {{ baseFilter }}
      <template v-if="marksAvailable">
        {{ markFilter }}
      </template>
    </pre>
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
import {debounce} from 'quasar';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method
const queryStore = useQueryStore();
const api = useApi();

const loading = ref(false);
const result = ref<string|null>(null); // todo: type
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

async function loadResults() {
  if (! isValid.value) {
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
    await api.post<typeof data, string>('queries/find-results', data)
      .then((resp: string) => result.value = resp) // todo: type
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
</script>

<style>

</style>

<template>
  <q-page padding>
    <TheQueryMenu/>
    <TheFilter/>
    <TheResults/>
    <SpinLoader
      v-if="loading"
    />
  </q-page>
</template>

<script lang="ts" setup>
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import TheFilter from 'components/Query/Filter/TheFilter.vue';
import TheResults from 'components/Query/Result/TheResults.vue';
import TheQueryMenu from 'components/Query/Menu/TheQueryMenu.vue';
import {onMounted, ref} from 'vue';
import {useQueryStore} from 'stores/query';
import useApi from 'src/composables/api';
import SpinLoader from 'components/Util/SpinLoader.vue';
import {Query} from 'src/models/query/query';
import {FilterNode} from 'src/models/query/filterNode';

const props = defineProps({
  id: String
});

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()
const store = useQueryStore();
const api = useApi();

const loading = ref(false);

layout.setToolbarTitle(t('queries.title'))
layout.setToolbarTabs([])
layout.setToolbarBreadcrumbs([])

function initQueryData() {
  if ('new' === props.id) {
    initNewQuery();
  } else {
    void loadQueryData();
  }
}

function initNewQuery() {
  store.queryCode = '';
  store.attemptedToSaveQuery = false;

  loading.value = false;
}

async function loadQueryData() {
  loading.value = true;

  const id = props.id as string;
  const query = await api.get<Query>(`queries/view/${id}`, () => loading.value = false) as Query;
  loading.value = true;

  await store.setQueryGroupById(query.query_group_id);
  store.queryCode = query.code;
  store.queryDescription = query.description || '';
  store.attemptedToSaveQuery = false;
  store.baseTable = query.raw_query.baseTable;
  store.setBaseFilter(FilterNode.FromJSON(query.raw_query.baseFilter));
  store.setVisibleColumns(query.raw_query.visibleColumns);
  store.setShowRowsWithoutMarks(query.raw_query.showRowsWithoutMarks);
  if (query.raw_query.markFilter) {
    store.markFilter = FilterNode.FromJSON(query.raw_query.markFilter);
  }

  loading.value = false;
}

onMounted(() => initQueryData());
</script>

<style>

</style>

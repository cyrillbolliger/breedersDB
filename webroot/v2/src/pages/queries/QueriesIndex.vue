<template>
  <q-page padding>
    <div class="row items-center">
      <h5 class="q-my-sm">{{ t('queries.query') }}</h5>

      <q-btn
        :label="t('queries.add')"
        color="primary"
        to="/queries/new"
        size="sm"
        outline
        class="q-mx-md"
      />
    </div>
    <TabularList
      :filter-function="filterFunction"
      :item-height="35"
      :items="queries"
      :loading="loading"
      max-list-height="calc(100vh - 260px)"
      @refresh="loadQueries"
    >
      <template #default="slotProps">
        <q-item
          v-ripple
          clickable
          @click.stop="$router.push(`/queries/${slotProps.item.id}`)"
        >
          <q-item-section>
            <q-item-label>
              <span class="text-grey-6 text-weight-bold">
                {{ getQueryGroupCodeFromQuery(slotProps.item) }} /
              </span>
              {{slotProps.item.code}}
            </q-item-label>
            <q-item-label caption>{{ slotProps.item.description }}</q-item-label>
          </q-item-section>
        </q-item>
      </template>
    </TabularList>
  </q-page>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import TabularList from 'components/Util/TabularList.vue';
import {computed, onMounted, ref} from 'vue';
import type {Query} from 'src/models/query/query';
import type {QueryGroup} from 'src/models/queryGroup';
import {useQueryStore} from 'stores/query';
import useApi from 'src/composables/api';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();
const api = useApi();

const loading = ref(true);
const queries = ref<Query[]>([]);

const queryGroups = computed<Map<number, QueryGroup>>(() => {
  const map = new Map<number, QueryGroup>();
  store.queryGroups.forEach((item: QueryGroup) => map.set(item.id, item));
  return map;
});

function getQueryGroupCodeFromQuery(item: Query) {
  return queryGroups.value.get(item.query_group_id).code
}

function filterFunction(term: string, item: Query) {
  if ( ! term) {
    return true
  }

  term = term
    .trim()
    .replace(/\s*\/\s*/, '/')
    .toLowerCase();

  const queryGroupName = getQueryGroupCodeFromQuery(item);
  const path = `${queryGroupName}/${item.code}`
    .trim()
    .toLowerCase();

  return path.indexOf(term) > -1
}

async function loadQueries() {
  loading.value = true;
  const resp = await api.get<Query[]>('/queries');
  queries.value = resp as Query[];
  loading.value = false;
}

onMounted(() => {
  void store.maybeLoadQueryGroups();
  void loadQueries();
});

</script>

<style scoped>

</style>

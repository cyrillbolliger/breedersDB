<template>
  <q-card style="min-width: 300px">
    <q-card-section>
      <div class="text-h6 q-pb-sm">{{ t('queries.editGroups') }}</div>
      <QueryGroupEditItem
        v-for="group in groups"
        :key="group.id"
        :item="group"
      />
    </q-card-section>

    <q-card-section>
      <div class="text-h6">{{ t('queries.addQueryGroup') }}</div>
      <QueryGroupAddItem/>
    </q-card-section>

    <q-separator/>

    <q-card-actions align="right" class="text-primary">
      <q-btn v-close-popup flat :label="t('general.close')"/>
    </q-card-actions>
  </q-card>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {computed, onMounted, ref} from 'vue';
import {useQueryStore} from 'stores/query';
import {QueryGroup} from 'src/models/queryGroup';
import QueryGroupEditItem from 'components/Query/Menu/QueryGroupEditItem.vue';
import QueryGroupAddItem from 'components/Query/Menu/QueryGroupAddItem.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const loading = ref(false);

const groups = computed<QueryGroup[]>(() => store.queryGroups);

async function ensureQueryGroupsLoaded() {
  loading.value = true;
  await store.maybeLoadQueryGroups();
  loading.value = false;
}

onMounted(() => ensureQueryGroupsLoaded());
</script>

<style scoped>

</style>

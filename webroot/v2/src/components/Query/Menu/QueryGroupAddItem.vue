<template>
  <q-input
    v-model="code"
    maxlength="120"
    dense
    outlined
    :placeholder="t('queries.queryGroupName')"
    @keyup.enter="save"
    :loading="loading"
    :error-message="code.length ? t('queries.queryGroupSaveFailed') : t('general.form.required')"
    :error="error"
  >
    <template
      #append
      v-if="!loading"
    >
      <q-btn
        icon="save"
        flat
        dense
        color="primary"
        @click="save"
        :title="t('general.save')"
      />
    </template>
  </q-input>
</template>

<script lang="ts" setup>
import {QueryGroup} from 'src/models/queryGroup';
import {ref} from 'vue';
import {useI18n} from 'vue-i18n';
import useApi from 'src/composables/api';
import {useQueryStore} from 'stores/query';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const loading = ref(false);
const error = ref(false);
const code = ref('');

async function save() {
  loading.value = true;

  const data: QueryGroup = {
    code: code.value.trim(),
  } as QueryGroup;

  await useApi()
    .post<QueryGroup, QueryGroup>('query-groups/add', data, () => null, {}, false)
    .then(() => {
      void store.forceLoadQueryGroups()
        .then(() => {
        error.value = false;
        code.value = '';
      })
    })
    .catch(() => error.value = true);

  loading.value = false;
}

</script>

<style scoped>

</style>

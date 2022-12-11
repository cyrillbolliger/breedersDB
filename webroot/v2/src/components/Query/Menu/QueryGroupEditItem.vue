<template>
  <q-input
    v-if="edit"
    v-model="code"
    :error="error"
    :error-message="t('queries.queryGroupSaveFailed')"
    :loading="loading"
    autofocus
    dense
    maxlength="120"
    outlined
    @keyup.enter="save"
    @keydown.stop.esc="abort"
  >
    <template
      v-if="!loading"
      #append
    >
      <q-btn
        :title="t('general.save')"
        color="primary"
        dense
        flat
        icon="save"
        @click="save"
      />
    </template>
  </q-input>
  <div
    v-else
    class="row no-wrap justify-between q-py-sm query-group-edit-item"
  >
    {{ item.code }}
    <div class="row">
      <q-btn
        :title="t('general.edit')"
        color="primary"
        dense
        flat
        icon="mode_edit"
        round
        size="sm"
        @click="activateEditMode"
      />
      <q-btn
        v-if="!loading"
        :title="t('general.delete')"
        color="negative"
        dense
        flat
        icon="delete_outline"
        round
        size="sm"
        @click="deleteItem"
      />
      <div
        v-else
        class="q-px-xs"
      >
        <q-spinner/>
      </div>
    </div>
  </div>

</template>

<script lang="ts" setup>

import {QueryGroup} from 'src/models/queryGroup';
import {computed, PropType, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import useApi from 'src/composables/api';
import {useQueryStore} from 'stores/query';

const props = defineProps({
  item: Object as PropType<QueryGroup>
});

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();

const item = computed(() => props.item as QueryGroup);

const loading = ref(false);
const edit = ref(false);
const error = ref(false);
const code = ref(item.value.code);

function activateEditMode() {
  edit.value = true
}

function abort() {
  code.value = item.value.code;
  edit.value = false;
  error.value = false;
}

async function save() {
  loading.value = true;

  const data: QueryGroup = {
    id: item.value.id,
    code: code.value.trim(),
  } as QueryGroup;

  await useApi()
    .patch<QueryGroup, QueryGroup>(`query-groups/edit/${item.value.id}`, data, () => null, {}, false)
    .then(() => {
      void store.forceLoadQueryGroups()
        .then(() => {
          edit.value = false;
          error.value = false;
        })
    })
    .catch(() => {
      error.value = true;
    })
    .then(() => loading.value = false);
}

async function deleteItem() {
  loading.value = true;
  await useApi().delete(`query-groups/delete/${item.value.id}`)
    .then(() => store.forceLoadQueryGroups());
  loading.value = false;
}

</script>

<style scoped>
.query-group-edit-item:hover {
  color: var(--q-primary);
  font-weight: bold;
}
</style>

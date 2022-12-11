<template>
  <q-select
    :borderless="!hasFocus && !changed"
    :disable="loading"
    :loading="loading"
    :model-value="group"
    :option-label="(item: QueryGroup) => item.code"
    :option-value="(item: QueryGroup) => item.id"
    :options="options"
    :outlined="hasFocus || changed || !group"
    :hide-dropdown-icon="!!group"
    @blur="hasFocus = false"
    @focus="hasFocus = true"
    @update:model-value="change"
    :error="!group"
    :class="{'query-group__select--no-group': !group}"
    :error-message="t('queries.selectQueryGroup')"
  >
    <template #after-options>
      <q-btn
        :label="t('queries.editGroups')"
        class="q-px-md q-py-sm full-width"
        color="primary"
        flat
        square
        @click="edit = true"
      />
    </template>
  </q-select>

  <q-dialog v-model="edit" persistent>
    <QueryGroupEdit/>
  </q-dialog>
</template>

<script lang="ts" setup>
import {computed, onMounted, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import {useQueryStore} from 'stores/query';
import type {QueryGroup} from 'src/models/queryGroup';
import QueryGroupEdit from 'components/Query/Menu/QueryGroupEdit.vue';
import {useRoute} from 'vue-router';

defineProps({
  changed: Boolean,
});

const emit = defineEmits<{
  (e: 'update:changed', value: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();
const route = useRoute();

const hasFocus = ref(false);
const loading = ref(false);
const edit = ref(false);

const options = computed<QueryGroup[]>(() => store.queryGroups);
const group = computed<QueryGroup>({
  get: (): QueryGroup => store.queryGroup as QueryGroup,
  set: (group: QueryGroup) => store.queryGroup = group,
});

function change(val: QueryGroup) {
  group.value = val;
  emit('update:changed', true);
}

async function ensureQueryGroupsLoaded() {
  loading.value = true;
  await store.maybeLoadQueryGroups();
  loading.value = false;
}

function setInitialQueryGroup() {
  if (route.params.id !== 'new') {
    return;
  }

  if ( ! group.value && options.value.length) {
    group.value = options.value[0];
  }
}

onMounted(() => {
  void ensureQueryGroupsLoaded()
    .then(setInitialQueryGroup);
});
</script>

<style scoped>
/*noinspection CssUnusedSymbol*/
.q-select {
  max-width: 280px;
  font-size: 1.5rem;
  margin-top: 20px;
}

.query-group__select--no-group {
  min-width: 150px;
}
</style>

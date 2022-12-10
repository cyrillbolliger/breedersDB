<template>
  <q-select
    :borderless="!hasFocus && !changed"
    :disable="loading"
    :loading="loading"
    :model-value="group"
    :option-label="(item: QueryGroup) => item.code"
    :option-value="(item: QueryGroup) => item.id"
    :options="options"
    :outlined="hasFocus || changed"
    hide-dropdown-icon
    @blur="hasFocus = false"
    @focus="hasFocus = true"
    @update:model-value="change"
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
import {computed, onMounted, PropType, ref} from 'vue';
import {useI18n} from 'vue-i18n';
import {useQueryStore} from 'stores/query';
import type {QueryGroup} from 'src/models/queryGroup';
import QueryGroupEdit from 'components/Query/Menu/QueryGroupEdit.vue';

const props = defineProps({
  group: Object as PropType<QueryGroup>,
  changed: Boolean,
});

const emit = defineEmits<{
  (e: 'update:group', value: QueryGroup): void
  (e: 'update:changed', value: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();
const hasFocus = ref(false);
const loading = ref(false);
const edit = ref(false);

const options = computed<QueryGroup[]>(() => store.queryGroups);

function change(val: QueryGroup) {
  emit('update:group', val)
  emit('update:changed', true);
}

async function ensureQueryGroupsLoaded() {
  loading.value = true;
  await store.maybeLoadQueryGroups();
  loading.value = false;
}

function setInitialQueryGroup() {
  if ( ! props.group && options.value.length) {
    emit('update:group', options.value[0]);
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
}
</style>

<template>
  <q-select
    :borderless="!hasFocus && !hasChanged"
    :disable="loading"
    :loading="loading"
    :model-value="modelValue"
    :option-label="(item: QueryGroup) => item.code"
    :option-value="(item: QueryGroup) => item.id"
    :options="options"
    :outlined="hasFocus || hasChanged"
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
import {QueryGroup} from 'src/models/queryGroup';
import QueryGroupEdit from 'components/Query/Menu/QueryGroupEdit.vue';

const props = defineProps({
  modelValue: Object as PropType<QueryGroup>,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: QueryGroup, change: boolean): void
}>();

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useQueryStore();
const hasFocus = ref(false);
const loading = ref(false);
const hasChanged = ref(false);
const edit = ref(false);

const options = computed<QueryGroup[]>(() => store.queryGroups);

function change(val: QueryGroup) {
  hasChanged.value = true;
  emit('update:modelValue', val, true)
}

async function ensureQueryGroupsLoaded() {
  loading.value = true;
  await store.maybeLoadQueryGroups();
  loading.value = false;
}

function setInitialQueryGroup() {
  if ( ! props.modelValue && options.value.length) {
    emit('update:modelValue', options.value[0], false);
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

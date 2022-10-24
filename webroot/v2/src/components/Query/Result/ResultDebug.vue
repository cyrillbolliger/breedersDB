<template>
  <q-btn
    v-if="expanded === false"
    class="q-mt-lg"
    color="primary"
    dense
    flat
    no-caps
    size="sm"
    @click="expanded = true"
  >{{ t('queries.debugShow') }}
  </q-btn>
  <div
    v-if="expanded === true"
    class="bg-grey-3 q-pa-md q-mt-md q-mb-md"
  >
    <code>{{ data?.sql }}</code>

    <template v-if="sqlParams.length">
      <p class="q-mt-md q-mb-none">Params:</p>
      <code v-for="param in sqlParams" :key="param">{{ param }}</code>
    </template>

    <q-btn
      v-if="expanded === true"
      class="q-mt-sm"
      color="primary"
      size="sm"
      @click="expanded = false"
    >{{ t('queries.debugHide') }}
    </q-btn>
  </div>
</template>
<script lang="ts" setup>

import {computed, PropType, ref} from 'vue';
import {QueryResponseDebug} from 'src/models/query/query';
import {useI18n} from 'vue-i18n';

const {t} = useI18n(); // eslint-disable-line @typescript-eslint/unbound-method

const props = defineProps({
  data: {
    type: Object as PropType<QueryResponseDebug>,
    default: () => ({sql: '', params: {}} as QueryResponseDebug),
  },
})

const expanded = ref(false);

const sqlParams = computed<string[]>(() => {
  const params = (<any>Object).values(props.data?.params) || []; // eslint-disable-line
  return params.map(param => `${param.placeholder}: ${param.value} (${param.type})`); // eslint-disable-line
});

</script>
<style scoped>
code {
  display: block;
}
</style>

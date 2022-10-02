<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('queries.results') }}</h5>

  <pre>
    {{ baseFilter }}
    {{ markFilter }}
  </pre>
</template>

<script setup lang="ts">
import {useI18n} from 'vue-i18n';
import {computed} from 'vue';
import {useQueryStore} from 'stores/query';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const queryStore = useQueryStore()

const baseFilter = computed(() => queryStore.baseFilter);
const markFilter = computed(() => queryStore.markFilter);
</script>

<style>

</style>

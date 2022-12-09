<template>
  <h5 class="q-mb-sm q-mt-sm">{{ t('queries.query') }}</h5>

  <q-btn
      to="/queries/new"
      color="primary"
      :label="t('queries.new')"
    />
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
</script>

<style>

</style>

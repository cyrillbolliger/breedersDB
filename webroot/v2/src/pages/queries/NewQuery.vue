<template>
  <q-page padding>
    Hello
  </q-page>
</template>

<script setup lang="ts">
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()

layout.setToolbarTitle(t('queries.title'))
layout.setToolbarTabs([])
layout.setToolbarBreadcrumbs([])


</script>

<style>

</style>

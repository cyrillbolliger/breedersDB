<template>
  <q-page padding>
    <TheQueryMenu />
    <TheFilter />
    <TheResults />
  </q-page>
</template>

<script setup lang="ts">
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import TheFilter from 'components/Query/Filter/TheFilter.vue';
import TheResults from 'components/Query/Result/TheResults.vue';
import TheQueryMenu from 'components/Query/Menu/TheQueryMenu.vue';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const layout = useLayout()

layout.setToolbarTitle(t('queries.title'))
layout.setToolbarTabs([])
layout.setToolbarBreadcrumbs([])

</script>

<style>

</style>

<template>
  <q-page padding>
    <router-view></router-view>
  </q-page>
</template>

<script lang="ts" setup>
import useMarkTabNav from 'src/composables/marks/tab-nav';
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import {watch} from 'vue';
import useMarkType from 'src/composables/marks/type';
import {LayoutTabsInterface} from 'src/models/layout';

const {setToolbarTitle, setToolbarTabs, setToolbarBreadcrumbs} = useLayout()
const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const type = useMarkType()

const tabs = useMarkTabNav()
setTabs(tabs.value)
watch(tabs, (newTabs) => setTabs(newTabs))

setTitle(type.value)
watch(type, (newType) => setTitle(newType))

setBreadcrumbs()

function setTabs(tabs: LayoutTabsInterface[]) {
  setToolbarTabs(tabs)
}

function setTitle(type: string) {
  const title = type === 'variety'
    ? t('marks.title.variety')
    : type === 'batch'
      ? t('marks.title.batch')
      : t('marks.title.tree')

  setToolbarTitle(title)
}

function setBreadcrumbs() {
  setToolbarBreadcrumbs([])
}
</script>

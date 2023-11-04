<template>
  <slot/>
</template>

<script lang="ts" setup>
import useMarkTabNav from 'src/composables/marks/tab-nav';
import useLayout from 'src/composables/layout';
import {useI18n} from 'vue-i18n';
import {useRoute} from 'vue-router';
import {watch} from 'vue';
import useMarkType from 'src/composables/marks/type';

const {setToolbarTitle, setToolbarTabs, setToolbarBreadcrumbs} = useLayout()
const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const type = useMarkType()

setToolbarTabs(useMarkTabNav())
setToolbarBreadcrumbs([])
setTitle(type.value)

watch(() => type.value, (newType) => setTitle(newType))

function setTitle(type: string) {
  const title = type === 'variety'
    ? t('marks.title.variety')
    : type === 'batch'
      ? t('marks.title.batch')
      : t('marks.title.tree')

  setToolbarTitle(title)
}

</script>

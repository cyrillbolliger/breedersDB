<template>
  <q-page padding>
    <!-- content -->
    <h1>{{ t('test.title')}}</h1>
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'src/store';

export default defineComponent({
  // name: 'PageName'
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const $store = useStore()

    void $store.dispatch('layout/title', t('test.title'))

    return {
      t
    }
  }
})
</script>

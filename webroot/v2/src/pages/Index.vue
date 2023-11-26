<template>
  <q-page class="row items-center justify-evenly">
    <q-btn
      to="/marks/tree/select-form"
      color="primary"
      :label="t('marks.title.tree')"
    />
    <q-btn
      to="/marks/variety/select-form"
      color="primary"
      :label="t('marks.title.variety')"
    />
    <q-btn
      to="/marks/batch/select-form"
      color="primary"
      :label="t('marks.title.batch')"
    />
    <q-btn
      to="/queries"
      color="primary"
      :label="t('queries.title')"
    />
  </q-page>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {useI18n} from 'vue-i18n';

export default defineComponent({
  name: 'PageIndex',
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    return {
      t
    }
  }
});
</script>

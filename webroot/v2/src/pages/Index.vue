<template>
  <q-page class="row items-center justify-evenly">
    <q-btn
      to="/marks/select-form"
      color="primary"
      :label="t('marks.title')"
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

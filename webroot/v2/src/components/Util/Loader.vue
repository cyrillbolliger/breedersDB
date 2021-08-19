<template>
  <div
    class="fixed-center"
  >
    <q-spinner
      size="3em"
      color="primary"
    />
    <p class="text-grey">{{t('general.loading')}}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import {useI18n} from 'vue-i18n';

export default defineComponent({
  name: 'Util/Loader',

  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    return {
      t
    }
  }

})
</script>

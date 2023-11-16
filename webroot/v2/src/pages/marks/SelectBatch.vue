<template>
  <MarkLayout>
    <q-page padding>

      <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectBatch.title') }}</h5>

      <div class="q-gutter-md">
        <BatchSelector
          @selected="setBatch"
        />
      </div>

    </q-page>
  </MarkLayout>
</template>

<script lang="ts">
import {defineComponent} from 'vue'
import {useI18n} from 'vue-i18n';
import {useMarkStore} from 'stores/mark';
import MarkLayout from 'components/Mark/MarkLayout.vue';
import {useRouter} from 'vue-router';
import {Notify} from 'quasar';
import BatchSelector from 'components/Batch/BatchSelector.vue';
import { Batch } from 'src/models/batch';

export default defineComponent({
  name: 'SelectVariety',
  components: {BatchSelector,  MarkLayout},
  setup() {
    const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
    const store = useMarkStore()
    const router = useRouter()

    function setBatch(batch: Batch | undefined | null){
      if (!batch) {
        Notify.create({
            message: t('general.failedToLoadData'),
            color: 'negative',
            closeBtn: true
          })
        return
      }

      store.setBatch(batch)
      void router.push('/marks/batch/mark-batch')
    }

    return {
      t,
      setBatch,
    }
  },
})
</script>

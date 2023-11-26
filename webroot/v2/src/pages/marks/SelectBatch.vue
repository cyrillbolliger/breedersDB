<template>
  <h5 class="q-mb-lg q-mt-sm">{{ t('marks.selectBatch.title') }}</h5>

  <div class="q-gutter-md">
    <BatchSelector
      @selected="setBatch"
    />
  </div>
</template>

<script lang="ts" setup>
import {useI18n} from 'vue-i18n';
import {useMarkStore} from 'stores/mark';
import {useRouter} from 'vue-router';
import {Notify} from 'quasar';
import BatchSelector from 'components/Batch/BatchSelector.vue';
import {Batch} from 'src/models/batch';

const {t} = useI18n() // eslint-disable-line @typescript-eslint/unbound-method
const store = useMarkStore()
const router = useRouter()

function setBatch(batch: Batch | undefined | null) {
  if ( ! batch) {
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
</script>
